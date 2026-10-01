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
- **Consommation mesurée en local** (01/10, base TSI, ~34 req/s) : pod PHP ~260 Mo au repos, **383 Mo en pic mais 4 à 5 cœurs** ; nginx 9 Mo ; worker ~60 Mo ; Redis ~30 Mo. Soit ~650 Mo au repos et ~900 Mo en pic par client (2 pods, hors base) : **EDEN est limité par le CPU, pas par la RAM**. Les réservations mémoire de `deploy_k8s` (PHP 1 Gi / 3 Gi, Redis 128 Mi / 512 Mi) sont larges : à ajuster après les tests de perf sur le cluster. Les clés de cache EDEN n'expirent pas (Redis `volatile-lru` ne peut évincer que les sessions) : surveiller la taille du cache.
- **Cluster POC** (01/10) : MKS 1.35, 2 à 5 nœuds r3-32 en autoscaling. **File Storage n'est pas branché par défaut** : un partage créé dans l'interface + `csi-driver-nfs` (1 partage, 1 sous-dossier par client, sans identifiant OpenStack ; Manila CSI aurait créé un partage par PVC). Mesuré : NFS 4.1, pas de root squash, écriture 93 Mo/s (plafond 128), lecture 398 Mo/s, 500 petits fichiers en 5 s, réécriture vue immédiatement par l'autre nœud.
- **Premier client sur le cluster (01/10) : TSI en ligne** — base migrée (455 tables, 1,5 Go) importée dans la MariaDB commune, `storage/app` réel (40 Go, 66 000 fichiers) copié sur le partage NFS, Job `eden:migrate` en 18 s, 2 pods web sur 2 nœuds, page de connexion en ~0,2 s. Filtre IP (403 hors IP autorisées), sorties réseau bloquées (pas de mail possible), HTTP → HTTPS. Pièges rencontrés : jeton GHCR (classique `read:packages` obligatoire), `max_allowed_packet` à relever (mails volumineux en base : 256 Mo), variables de service Kubernetes (`REDIS_PORT=tcp://…`, d'où `enableServiceLinks: false`), fichiers restaurés en 700 (→ 644/755).
- **Tests de montée en charge sur le cluster (01/10, TSI, k6 `montee`, 120 VUs, rythme ×3 ≈ 115× le pic réel)** :
  - 1er passage (HPA 2→6, 8 workers FPM) : HPA bloqué à 6 dès 20 VUs, 2 nœuds saturés, autoscaler de nœuds jamais déclenché ; p95 pages 8,1 s, médiane 194 ms, 43 req/s, 0 % d'erreur.
  - 2e passage (HPA 2→15, 4 workers FPM `ondemand`) : 2→15 pods, **nœuds ajoutés automatiquement** (3e ~3 min après les premiers pods en attente, puis 4e) ; p95 2,6 s, médiane 140 ms, 48,6 req/s, 0 % d'erreur. MariaDB jusqu'à ~1,9 cœur (réserve 1 → à relever).
  - ⚠ **Sessions perdues sous charge** (renvois vers la connexion) : 779 puis 1 731 avec plus de pods.
    - Cause trouvée : un **scale down de nœuds a évincé Redis** (sans persistance) → toutes les sessions perdues. Corrigé : 2 pools de nœuds (`socle` fixe pour MariaDB/Redis/Traefik/worker, `web` autoscalé pour les pods web), Redis et MariaDB `safe-to-evict: false`, Redis persisté (AOF).
    - Reste un taux faible (3 à 9 pour 1000 requêtes) **indépendant de Kubernetes** : identique avec 1 pod et 6 pods, avec 1 ou 20 comptes, sans redémarrage ni éviction Redis ; doublé par les rafales AJAX parallèles (écrasement de session entre requêtes simultanées, EDEN stocke beaucoup en session ?). À confirmer en jouant le même scénario k6 sur l'existant (VM QA) : si les pertes y sont aussi, c'est un comportement EDEN antérieur au POC (dev).

## Pistes pour un prochain POC (hors objectif actuel)
- OPcache est déjà actif en prod : les gains de perf ne viennent pas des conteneurs. Vérifier la même config en QA avant le test de charge.
- `app/Migrations` n'est lu que pour appliquer des migrations (fichiers → base), pas à chaque requête : il pourrait vivre dans le dépôt et l'image du client (option b de `08`) plutôt que sur le volume partagé.
- Les `storage/app/eden_*.php` sont inclus à chaque requête et exclus d'OPcache : coût de lecture sur NFS à mesurer (tests 2 et 4). Si trop cher : passage du paramétrage en base (option c).
- **Écritures atomiques** du PHP et des composants générés (`storage/app`, `app/Migrations`) : un pod peut lire un fichier en cours d'écriture par l'autre. Risque faible (écritures rares, faites par l'équipe) ; pas de correctif : la cible les supprime.
- **Cible (dev)** : plus aucun code généré sur le stockage partagé. Les composants JS/CSS ne sont plus écrits dans `storage/app/public`, et le paramétrage/les migrations passent en base. Le volume partagé ne garde que les fichiers métier (pièces jointes, exports).
- **Crons → tâches planifiées Laravel** (`schedule:run`) : pour le POC, `php artisan eden:cron <tâche>` + un CronJob Kubernetes par tâche. À terme (dev), déclarer les tâches dans le scheduler Laravel, avec `withoutOverlapping()` / `onOneServer()`, et un seul CronJob par client.
- L'écran de maintenance « tester les crons » appelle les crons en HTTP : il ne fonctionne plus avec `/eden/cron/*` fermé dans nginx.
- **Infra à choisir / analyser (post-POC)** : interface d'admin et accès des collègues → OVH Managed Rancher Service (85 € HT/mois minimum pour 20 vCPU, à chiffrer sur ~44 vCPU) ou Headlamp + OIDC (0 €, plus de configuration). Voir `07`, §3.
- **MariaDB : plusieurs instances moyennes plutôt qu'une grosse ?** Une instance commune porte TSI à ~1,9 cœur à 115× son pic : pour 40 clients, une seule instance concentre le risque (blocage, voisin bruyant, export ou migration de plusieurs heures, maintenance commune). Piste : quelques instances (groupes de 8-10 petits clients, instance dédiée pour les gros comme capvisio), placées sur le socle, industrialisées avec `mariadb-operator`. Surcoût : un buffer pool par instance (~0,5-1 Go fixe), plus d'objets à exploiter ; IOPS des volumes OVH liées à leur taille (à vérifier). Découpage à décider avec les mesures VictoriaMetrics ; déplacer un client = dump + restauration + `DB_HOST`. Test POC possible : 2 instances × 2-3 clients, k6 en parallèle pour mesurer l'isolation.
