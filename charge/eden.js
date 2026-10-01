// Test de charge EDEN : rejoue les sessions reelles de log_page (GET), avec leur rythme.
// Meme script pour la reference (VM actuelle) et le POC Kubernetes : seule BASE_URL change.
//
//   docker run --rm -i --network host --user "$(id -u):$(id -g)" -v "$PWD:/w" -w /w \
//     -e BASE_URL=https://tsi.poc.ateja.corp -e EDEN_EMAIL=... -e EDEN_PASSWORD=... \
//     -e PARCOURS=../.local/tsi/parcours.json -e PROFIL=reference \
//     grafana/k6 run charge/eden.js
//
// Variables :
//   BASE_URL       racine du client (https://...)
//   EDEN_EMAIL     compte de test SANS double authentification (jamais dans git)
//   EDEN_PASSWORD
//   PARCOURS       JSON produit par charge/extraire_parcours.py (chemin relatif a charge/)
//   PROFIL         reference | montee | stress   (voir PROFILS)
//   ACCELERATION   divise les pauses entre pages (defaut 1 = rythme reel)
//   ASSETS         1 (defaut) : charge JS/CSS/images des pages HTML, avec cache par VU
import http from 'k6/http';
import { check, sleep } from 'k6';
import { SharedArray } from 'k6/data';
import { Counter } from 'k6/metrics';

const BASE_URL = (__ENV.BASE_URL || 'https://localhost:8443').replace(/\/$/, '');
const ACCELERATION = parseFloat(__ENV.ACCELERATION || '1');
const AVEC_ASSETS = (__ENV.ASSETS || '1') === '1';
const PROFIL = __ENV.PROFIL || 'reference';

const sessions = new SharedArray('sessions', () => JSON.parse(open(__ENV.PARCOURS || '../.local/tsi/parcours.json')).sessions);

const reconnexions = new Counter('eden_reconnexions');

// reference : ~2x le pic reel de TSI (4-5 utilisateurs simultanes), rythme reel
// montee    : monte par paliers pour declencher le HPA puis l'autoscaler de noeuds
// stress    : jusqu'a saturation, pauses divisees (ACCELERATION conseillee : 5 a 10)
const PROFILS = {
  reference: {
    executor: 'constant-vus', vus: 10, duration: '20m',
  },
  montee: {
    executor: 'ramping-vus', startVUs: 0,
    stages: [
      { duration: '3m', target: 20 }, { duration: '5m', target: 20 },
      { duration: '3m', target: 60 }, { duration: '7m', target: 60 },
      { duration: '3m', target: 120 }, { duration: '10m', target: 120 },
      { duration: '5m', target: 0 },   // redescente : observer le scale down
    ],
    gracefulRampDown: '1m',
  },
  stress: {
    executor: 'ramping-vus', startVUs: 0,
    stages: [
      { duration: '5m', target: 100 }, { duration: '5m', target: 200 },
      { duration: '5m', target: 400 }, { duration: '5m', target: 400 },
      { duration: '3m', target: 0 },
    ],
    gracefulRampDown: '1m',
  },
};

export const options = {
  insecureSkipTLSVerify: true,   // certificat auto-signe du POC
  scenarios: { eden: PROFILS[PROFIL] },
  thresholds: {
    http_req_failed: ['rate<0.01'],
    'http_req_duration{type:page}': ['p(95)<1500'],
    'http_req_duration{type:asset}': ['p(95)<500'],
  },
  summaryTrendStats: ['avg', 'med', 'p(90)', 'p(95)', 'p(99)', 'max'],
};

// Nom de requete normalise (sinon une serie de metriques par identifiant)
function motif(url) {
  return url.replace(/\/[0-9]+(?=\/|$)/g, '/{id}');
}

function connexion() {
  const page = http.get(`${BASE_URL}/eden/login`, { tags: { name: '/eden/login', type: 'page' } });
  // Deja connecte : /eden/login redirige vers l'application, pas de formulaire
  if (page.status === 200 && page.url && !page.url.includes('/eden/login'))
    return true;
  const jeton = (page.body || '').match(/name="_token"\s+value="([^"]+)"|name="csrf-token"\s+content="([^"]+)"/);
  if (!jeton) {
    check(page, { 'jeton CSRF trouve': () => false });
    return false;
  }
  const res = http.post(`${BASE_URL}/eden/login`, {
    _token: jeton[1] || jeton[2],
    email: __ENV.EDEN_EMAIL,
    password: __ENV.EDEN_PASSWORD,
  }, { tags: { name: 'POST /eden/login', type: 'page' } });

  const ok = check(res, { 'connexion reussie': (r) => r.status === 200 && !r.url.includes('/eden/login') && !r.url.includes('code_connexion') });
  return ok;
}

// Cache navigateur simule, par VU : un asset n'est charge qu'une fois
const cache = new Set();

function charger_assets(res) {
  if (!AVEC_ASSETS || !(res.headers['Content-Type'] || '').includes('text/html'))
    return;
  const urls = [];
  const re = /(?:src|href)="([^"]+\.(?:js|css|png|jpe?g|gif|svg|woff2?)(?:\?[^"]*)?)"/g;
  let m;
  while ((m = re.exec(res.body || '')) !== null) {
    let u = m[1];
    if (u.startsWith('//') || /^https?:\/\//.test(u) && !u.startsWith(BASE_URL))
      continue;   // CDN externes : hors perimetre
    if (u.startsWith('/')) u = BASE_URL + u;
    else if (!u.startsWith('http')) u = `${BASE_URL}/${u}`;
    if (!cache.has(u)) { cache.add(u); urls.push(u); }
  }
  // le navigateur charge en parallele (HTTP/2)
  for (let i = 0; i < urls.length; i += 20)
    http.batch(urls.slice(i, i + 20).map((u) => ['GET', u, null, { tags: { name: 'asset', type: 'asset' } }]));
}

let connecte = false;
// URLs du parcours qui renvoient vers la connexion (une trace par motif et par VU)
const motifs_vers_login = new Set();

export default function () {
  if (!connecte) {
    connecte = connexion();
    if (!connecte) { sleep(10); return; }
  }

  const session = sessions[Math.floor(Math.random() * sessions.length)];

  // Les pas a delai 0 partent ensemble (rafale AJAX a l'ouverture d'une fiche)
  let i = 0;
  while (i < session.length) {
    const groupe = [session[i]];
    while (i + groupe.length < session.length && session[i + groupe.length][0] === 0)
      groupe.push(session[i + groupe.length]);
    i += groupe.length;

    const pause = groupe[0][0] / ACCELERATION;
    if (pause > 0) sleep(Math.min(pause, 300));

    const reponses = http.batch(groupe.map(([, url]) =>
      ['GET', BASE_URL + url, null, { tags: { name: motif(url), type: 'page' }, redirects: 5 }]));

    for (let j = 0; j < reponses.length; j++) {
      const res = reponses[j];
      // renvoi vers la connexion (session perdue, ou URL qui l'exige) : on se reconnecte et on continue
      if (res.url && res.url.includes('/eden/login')) {
        const m = motif(groupe[j][1]);
        reconnexions.add(1, { motif: m });
        if (!motifs_vers_login.has(m)) {
          motifs_vers_login.add(m);
          console.warn(`vers login : ${m}`);
        }
        connecte = connexion();
        if (!connecte) return;
        continue;
      }
      check(res, { 'statut < 400': (r) => r.status > 0 && r.status < 400 });
      charger_assets(res);
    }
  }
}
