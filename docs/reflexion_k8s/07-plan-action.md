# 07 — Plan d'action

## 0. Tout de suite (indépendant de la migration)
- [ ] Rotation des secrets en clair dans `eden-install-clean.yml` + passage en vault + purge de l'historique (attention à `CLE_CRYPTAGE`).
- [ ] Correction de la règle de cycle de vie Glacier chez AWS (~40 $/mois perdus).
- [ ] Surveillance du remplissage Ceph (seuil sûr ~40 % par OSD).
- [ ] Migration des VMs Debian 11 → 12.
- [ ] Montée des ~8 clients PHP 7.4.
- [x] `mysqld_exporter` + dashboard `mariadb-overview` en PR et QA (28/09).
- [ ] Relecture des métriques MariaDB après ≥ 1 semaine (vers le 06/10, puis après la fin de mois) → dimensionnement définitif.

## 0bis. Chantiers préparatoires
- [~] **MariaDB : mesurer puis optimiser** — collecte en cours depuis le 28/09 (voir `03`) ; décision après relecture.
- [x] **EDEN « semi-stateful » : audit du code** → `08-eden-conteneurisation.md`.
- [x] PHP généré : volume partagé pour le POC et la migration, puis stockage en base.
- [ ] Corrections EDEN (Redis, crons, queues, proxy, image de prod…), à démarrer avant la migration.
- [ ] **Sécurité, tout de suite** : filtrer par IP les routes `migrations_installation` et `cron` ; sortir les secrets du code EDEN.

## 1. POC OVH
Voir **`09-poc.md`** : 5 clients EDEN en 2 pods sur RWX, sous charge, 8 critères go / no-go, ~5 semaines, ~700 € HT.
- [ ] Décision (2026-09-28) : PHP généré sur le volume partagé pour le POC (option a) ; stockage en base (option c) si tout est validé.

## 2. Migration (si POC validé)
| Étape | Contenu | Durée indicative |
|---|---|---|
| Socle | Clusters MKS QA + PR, GitOps, CI d'images | 1–2 semaines |
| QA | Migration des projets QA | 2 semaines |
| PR | Par vagues de 5–10 clients, de nuit : fichiers + base + bascule DNS. MariaDB d'abord conservée sur PVE via le vRack si besoin | 4–8 semaines |
| Décommission | Arrêt des serveurs PVE QA, puis PR (résiliation au mois). **Prérequis : les VMs Windows (Sage, ERP, AD) ont été rehébergées ailleurs** (hors périmètre de cette analyse) | — |

Coexistence des deux infras pendant 1 à 3 mois : prévoir ce surcoût temporaire.

Temps d'admin visé après stabilisation : ~20–25 h/mois (estimation, à mesurer après 3 mois de run).

## 3. Après le POC : interface d'admin (réflexion du 2026-09-30)
- **Rancher écarté** (30/09) : Manager = un cluster de plus à maintenir, simple tableau de bord sur MKS importé ; RKE2 = plan de contrôle à notre charge.
- **À réévaluer (01/10) : OVH Managed Rancher Service** — Rancher hébergé et exploité par OVH (installation, mises à jour, HA, sauvegardes), import et création de clusters MKS. Lève l'objection « cluster de plus à maintenir », et simplifie l'accès des collègues (fournisseurs d'identité Entra ID / GitHub intégrés, droits par projet). Prix : **85 € HT/mois minimum pour 20 vCPU** gérés (relevé le 01/10). La cible compte ~44 vCPU (PR 4 × r3-64 = 32, QA 12) : si la facturation suit les vCPU, on dépasse le minimum → coût réel à vérifier, à rapporter aux ~1 525 €/mois de la cible. À vérifier aussi : doublon avec ArgoCD/Fleet si GitOps. RKE2 reste écarté. **Choix post-POC.**
- **Cible si Rancher n'est pas retenu** : k9s (admin) + **Headlamp** en pod (logs, shell, redémarrage) + **Grafana/VictoriaLogs** pour les logs des collègues (y compris des pods redémarrés).
- **Authentification** : OIDC sur MKS et Headlamp, RBAC par groupe (collègues : lecture + logs ; devs : shell en QA ; admin : tout). Entra ID (app déclarée en Terraform `azuread`, secret à renouveler tous les 24 mois max) ou GitHub via Dex si seuls les devs ont besoin d'accès.
- Pas dans le POC.
