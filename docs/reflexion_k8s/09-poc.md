# 09 — POC OVH : plan et critères go / no-go

Décision du 2026-09-28 :
- POC avec **RWX** et le PHP généré sur le volume partagé (option **a**) ;
- si tout est validé → migration, puis passage du PHP généré en base (option **c**) ;
- **le POC doit justifier le temps de développement** : il faut plusieurs clients EDEN réels, sous charge.

## Périmètre
| Élément | POC |
|---|---|
| Cluster | MKS GRA (Free), 3 × r3-64 en facturation horaire, autoscaler plafonné à 4 |
| Stockage | OVH File Storage (RWX) : 1 partage, 1 sous-dossier par client ; block HS Gen2 pour MariaDB |
| Socle | ArgoCD (dépôt `argo`), Traefik, cert-manager, SOPS, Redis, `mariadb-operator`, stack VictoriaMetrics |
| EDEN | **5 clients standard** (image commune), bases et fichiers copiés à la main depuis la QA, **2 pods chacun**, workers `queue:work`, CronJobs. Le POC pourra ensuite être étendu à d'autres clients |
| Réseau | Dans le vRack, pour pouvoir comparer avec la prod et la QA actuelles |

Proposition de clients (à valider) :
| Client | Pourquoi |
|---|---|
| capvisio | Le plus de fichiers (141 Go), CPU le plus élevé |
| sra | CPU soutenu |
| mrpompes | Pics CPU (jusqu'à 4 cœurs) |
| centaure | Pics CPU, ~42 Go de fichiers |
| 1 petit client standard | Cas nominal |

Les clients avec du **code spécifique sont exclus du POC** : c'est un sujet de gestion d'images (1 dépôt = 1 image, construite par sa CI), traité dans la mise en place, pas dans la validation de l'infra.

## Correctifs EDEN minimum pour le POC (sous-ensemble de `08`)
Redis (sessions et cache), `TrustProxies` / HTTPS, image de prod (composer au build), `storage/app` + `app/Migrations` sur le RWX, écritures atomiques du PHP généré, worker de queue, crons en CronJob avec verrou atomique, logs sur stderr, Ghostscript.
→ **Le temps réellement passé sur ces correctifs sert d'étalon** pour recaler l'estimation globale (~6 à 9 semaines-développeur).

## Tests
| # | Test | Méthode | Critère go |
|---|---|---|---|
| 1 | **Fonctionnel** | Parcours métier joués par les devs sur chaque client : login, fiches, upload et téléchargement de pièces jointes, PDF, exports, modification du paramétrage no-code, crons, queues, mails | 100 % des parcours OK **avec 2 pods** (sessions conservées, paramétrage visible sur les 2 pods) |
| 2 | **Charge** | Scénario k6 (ou Locust) construit à partir des parcours ; **même test** joué sur la VM QA actuelle (référence) puis sur le POC ; jusqu'à 2 fois le pic mesuré | Latence p95 ≤ référence + 10 %, 0 erreur 5xx |
| 3 | **MariaDB** | sysbench + rejeu d'une journée type, sur Ceph actuel contre HS Gen2 ; `mysqld_exporter` | TPS et latence ≥ −10 % par rapport à l'actuel |
| 4 | **RWX** | Écritures concurrentes depuis 2 pods, propagation d'une modification du paramétrage, débit (plafond 128 Mo/s), latence des uploads | Aucune erreur ni fichier corrompu, propagation ≤ 5 s, débit ≥ 2 fois le pic PR (~18 Mo/s) |
| 5 | **Résilience** | Suppression d'un pod, drain d'un nœud, **suppression d'un nœud**, montée de version MKS, ajout d'un nœud par l'autoscaler | Fronts : **0 coupure** ; MariaDB : reprise ≤ 5 min ; nouveau nœud ≤ 10 min |
| 6 | **Backup / restauration** | Dump et restauration d'une base, restauration du partage d'un client (snapshot / Kopia) | Dans les RPO (1 h) et RTO (1 j) ; restauration d'un client < 1 h |
| 7 | **Coût** | Consommation réelle lue sur la facture OVH et extrapolée à 40 clients | ≤ 1 610 €/mois pour EDEN |
| 8 | **Exploitation** | Déploiement d'une version par PR GitHub → CI → ArgoCD, rollback | Déploiement et rollback sans intervention manuelle ni coupure |

**Go** si les 8 critères sont atteints. **No-go** partiel :
- 3 échoue → MariaDB sur instances à part (variante 3) ;
- 4 échoue → RWO (1 pod par client) ;
- 5 ou 7 échoue → réévaluation (Scaleway en plan B).

## Planning indicatif
**Une seule personne fait l'infra et le dev d'EDEN, en plus du run** (~50 h/mois). Le planning est donc séquentiel, à temps partiel.

| Phase | Contenu | Durée (temps partiel) |
|---|---|---|
| 1. Référence | `mysqld_exporter`, mesures de référence sur l'actuel (tests 2 et 3) | 1 semaine |
| 2. Socle | Cluster MKS, GitOps, Redis, opérateur MariaDB, File Storage, monitoring | 1–2 semaines |
| 3. EDEN minimum | Image de prod, Redis, proxies, RWX + PHP généré atomique, crons, queues, logs → **temps mesuré = étalon** | 2–4 semaines |
| 4. Clients | Copie des 5 bases et fichiers QA, déploiement en 2 pods | 1 semaine |
| 5. Tests | Tests 1 à 8, corrections | 1–2 semaines |
| 6. Bilan | Go / no-go, recalage des coûts et de l'effort | quelques jours |
| **Total** | | **~2 à 3 mois** |

Pour ne payer les nœuds que pendant les phases 4 et 5 : développer l'image EDEN en local (docker compose), puis créer le cluster.

## Budget du POC
Nœuds (3 × r3-64 à ~0,30 €/h) ≈ 650 € par mois allumé, plus File Storage, block et LB ≈ 60 €. **~700 à 1 000 € HT** si le cluster n'est allumé que pour les phases 2 (en partie), 4 et 5.

## Décisions
- Clients avec code spécifique : hors POC (gestion d'images).
- Données : 5 bases QA copiées à la main ; extension possible si les 5 passent.
- Ressources : 1 personne (infra + dev EDEN), avec l'aide de Claude pour les manifests, la CI et les corrections du code.

## Avancement : préparation locale (30/09 – 01/10)
Projet `eden_poc` (dépôt `ateja-alex/edenv2_poc`), branche `feat/poc-multipod`, avec un compose qui simule 2 pods derrière Traefik en HTTPS.
- Validé en local, sur la base de prod de TSI : 2 pods servent les mêmes pages en même temps, la session suit d'un pod à l'autre (connexion réelle), le paramétrage généré est visible sur les 2, `/storage` est servi.
- `php artisan eden:migrate` remplace la route publique `migrations_installation` : idempotent, verrouillé, lancé une fois par déploiement (Job, pas initContainer). Une base vide n'est pas prise en charge : un client part toujours d'un dump.
- **Durée** : la montée de version de TSI depuis sa version de prod a pris ~7 h 40 (script d'index de recherche `S20251020` : 4 h 08, autres scripts « après » : 3 h 32). À prévoir pour le Job : délai long, déploiements lourds de nuit, et demander aux devs de sortir ces reprises du chemin de déploiement.
- **API modèle** (`EDEN_MODEL_API_URL` / `EDEN_MODEL_API_KEY`) : utilisée par les utilisateurs Ateja et les licences, sautées si absente. Le Job de migration aura besoin d'une sortie réseau vers cette API et de la clé en Secret SOPS. Traductions retirées de la migration.
- Restauration d'un client : base **et** fichiers (`storage/app`, `app/Migrations`) du même instant ; passer les dossiers en 0755 (nginx ne tourne pas sous l'utilisateur PHP).

## Pistes pour un prochain POC (hors objectif actuel)
- OPcache est déjà actif en prod : les gains de perf ne viennent pas des conteneurs. Vérifier la même config en QA avant le test de charge.
- `app/Migrations` n'est lu que pour appliquer des migrations (fichiers → base), pas à chaque requête : il pourrait vivre dans le dépôt et l'image du client (option b de `08`) plutôt que sur le volume partagé.
- Les `storage/app/eden_*.php` sont inclus à chaque requête et exclus d'OPcache : coût de lecture sur NFS à mesurer (tests 2 et 4). Si trop cher : passage du paramétrage en base (option c).
- **Écritures atomiques** du PHP et des composants générés (`storage/app`, `app/Migrations`) : un pod peut lire un fichier en cours d'écriture par l'autre. Risque faible (écritures rares, faites par l'équipe) ; pas de correctif : la cible les supprime.
- **Cible (dev)** : plus aucun code généré sur le stockage partagé. Les composants JS/CSS ne sont plus écrits dans `storage/app/public`, et le paramétrage/les migrations passent en base. Le volume partagé ne garde que les fichiers métier (pièces jointes, exports).
- **Crons → tâches planifiées Laravel** (`schedule:run`) : pour le POC, `php artisan eden:cron <tâche>` + un CronJob Kubernetes par tâche. À terme (dev), déclarer les tâches dans le scheduler Laravel, avec `withoutOverlapping()` / `onOneServer()`, et un seul CronJob par client.
- L'écran de maintenance « tester les crons » appelle les crons en HTTP : il ne fonctionne plus avec `/eden/cron/*` fermé dans nginx.
