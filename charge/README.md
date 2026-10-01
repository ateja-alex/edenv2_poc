# Tests de charge EDEN (k6)

Rejoue la navigation **réelle** des utilisateurs, tirée de la table `log_page` d'EDEN :
sessions par utilisateur, dans l'ordre et avec le rythme d'origine. Le même script sert de
référence (VM actuelle) et de mesure sur le POC Kubernetes ; seule `BASE_URL` change.

Limites : `log_page` ne garde que l'URL (pas le corps des POST, ~50 % du trafic) → seuls les
**GET** sont rejoués, sans ceux qui écrivent (suppression, transformation, paramétrage…).
Les assets (JS, CSS, images) sont chargés comme par un navigateur, une fois par VU.

## 1. Extraire les parcours d'un client

Le fichier produit contient de vrais identifiants de fiches : il reste dans `.local/` (ignoré).

```bash
SQL=$(python3 charge/extraire_parcours.py --sql --depuis 2026-07-01 --jusqu-a 2026-09-30)
mariadb -N -B <base_du_client> -e "$SQL" | python3 charge/extraire_parcours.py > .local/<client>/parcours.json
```

TSI (juillet → septembre 2026) : 513 sessions, 52 000 GET ; session médiane de 65 requêtes en 5,7 min ;
pic réel ~1 350 requêtes/h (hors assets), 4-5 utilisateurs simultanés.

## 2. Compte de test

Un compte **sans double authentification**, dédié au test, dans `.local/<client>/k6.env` (ignoré) :

```
EDEN_EMAIL=...
EDEN_PASSWORD=...
```

En local, il a été créé dans la copie de la base TSI en clonant l'utilisateur le plus actif
(`k6@poc.invalid`, accès à toutes les entités). Jamais de compte réel dans un test de charge.

## 3. Lancer

```bash
set -a; . .local/tsi/k6.env; set +a
docker run --rm -i --network host --user "$(id -u):$(id -g)" -v "$PWD:/w" -w /w \
  -e BASE_URL=https://tsi.poc.ateja.corp -e EDEN_EMAIL -e EDEN_PASSWORD \
  -e PARCOURS=../.local/tsi/parcours.json -e PROFIL=montee \
  grafana/k6 run charge/eden.js
```

| `PROFIL` | Charge | Usage |
|---|---|---|
| `reference` | 10 VUs pendant 20 min, rythme réel (~2× le pic de TSI) | Comparaison VM actuelle / POC (critère : p95 ≤ référence + 10 %) |
| `montee` | paliers 20 → 60 → 120 VUs puis redescente | Déclencher le HPA, puis l'autoscaler de nœuds, et observer le scale down |
| `stress` | jusqu'à 400 VUs | Trouver la saturation (avec `ACCELERATION=5` à `10`) |

Autres variables : `ACCELERATION` (divise les pauses, défaut 1), `ASSETS=0` (pas d'assets).
Pour un test rapide : `grafana/k6 run --vus 3 --duration 1m …` (remplace le profil).

Plusieurs clients en même temps : un `k6 run` par client, en parallèle.

Seuils : moins de 1 % d'erreurs, p95 des pages < 1,5 s, p95 des assets < 500 ms.
Pendant le test, observer le cluster (voir `deploy_k8s/README.md`, §5).

## Premier essai (local, 01/10)

Compose multipod, base TSI, 3 VUs, `ACCELERATION=20`, 1 min : 1 453 requêtes, p95 pages 457 ms,
p95 assets 9,5 ms, 0,6 % d'erreurs (pièces jointes absentes en local).
