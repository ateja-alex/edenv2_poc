# Réflexion : passage de l'hébergement EDEN sur Kubernetes cloud

Analyse du 2026-09-28.

## Conclusion
- **Cible** : EDEN (fronts + MariaDB) sur **OVH Managed Kubernetes**, tout en instances, GitOps avec ArgoCD.
- **Coût** : **≈ 1 525 € HT/mois** contre ≈ 2 321 aujourd'hui (−34 %, ~9 500 €/an). Fourchette : 1 250 – 1 600 selon les leviers.
- **Configuration** : PR sur 4 × r3-64 (fronts et MariaDB sur les mêmes nœuds), QA sur 2 nœuds sans SLA, pas d'instance VPN.
- **Gains** :
  - plus d'hyperviseur, de Ceph, de 40 OS à patcher, ni de 192 IPs ;
  - déploiements par image et CI ;
  - SLA 99,99 % sur les instances.
- **Prérequis applicatif** : EDEN doit être corrigé pour tourner en plusieurs pods (~6 à 9 semaines-développeur, voir `08`).
- **Condition** : POC (5 clients, 2 pods, RWX, sous charge) validant fonctionnel, perf, MariaDB, résilience et coût → `09-poc.md`.
- Scaleway en plan B ; Infomaniak écarté pour la prod (voir `05-choix-fournisseur.md`) ; MySQL managé écarté.
- Windows (Sage, ERP) et VMs de dev : 1 serveur dédié à côté (≤ 300 €/mois).
- **Bilan global** : ≈ 1 825 €/mois (−21 %), ≈ 1 450 € avec scaling jour/nuit + Savings Plan (−38 %).

## Fichiers
| Fichier | Contenu |
|---|---|
| `01-existant.md` | Infra, inventaire, coûts, constats |
| `02-applications.md` | EDEN, conteneurisation, alerte secrets |
| `03-charge-exigences.md` | SLA / RPO / RTO, charge mesurée PR et QA, besoin cible |
| `04-contraintes-equipe.md` | Contraintes, équipe, priorités |
| `05-choix-fournisseur.md` | OVH retenu, Scaleway en plan B, raisons de l'éviction d'Infomaniak |
| `06-cible-ovh.md` | Architecture cible, chiffrage, leviers, incertitudes |
| `07-plan-action.md` | Actions immédiates, POC, migration |
| `08-eden-conteneurisation.md` | Audit du code EDEN : corrections pour 2 pods par client, volumes, sécurité |
| `09-poc.md` | Plan du POC OVH, tests et critères go / no-go |
| `plans/` | Plans à exécuter par Claude Code dans les dépôts (ex. dashboard MariaDB) |
| `annexes/` | CSV des VMs, script d'extraction de l'inventaire PVE |

Copie centralisée du dossier de réflexion. Restent **hors du dépôt**, dans le dossier d'origine : les factures OVH (coordonnées bancaires, identité de la société) et l'archive brute de l'inventaire PVE (régénérable avec `annexes/inventaire-pve.sh`).

Mise en œuvre du POC dans ce dépôt : images (`Dockerfile`), simulation locale à 2 pods (`docker-compose.multipod.yml`), manifests Kubernetes (`deploy_k8s/`).
