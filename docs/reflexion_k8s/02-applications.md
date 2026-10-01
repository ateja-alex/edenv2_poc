# 02 — Applications

Sources : réponses de l'utilisateur et dépôt `/home/alex/pro/repos/infra/ansible/`.

## EDEN
| Élément | Constat |
|---|---|
| Stack | PHP / Laravel 12, Nginx + PHP-FPM, Memcache, MariaDB |
| Modèle | 1 instance (VM) par client, 1 base par client sur le MariaDB de son pool |
| Code | 3 dépôts GitHub assemblés (squelette `std-eden` → dépôt propre au client, cœur `Eden`, `Eden_public`) |
| Clients | 40 en PR, 52 projets en QA. **~10 avec du code spécifique**, **~8 encore en PHP 7.4**, versions pas toutes alignées |
| Cadence | Mise à jour toutes les 3 semaines, LTS tous les ~6 mois, déploiement par `git pull` à la main, pas de CI/CD |
| Fichiers | Pièces jointes sur disque local (passage S3 abandonné : clients hybrides M-Files / SharePoint) |
| Tâches | Queues Laravel `database` sous Supervisor ; crons centralisés en appels HTTP |
| TLS | Terminé par Nginx sur chaque VM (certificats sur NFS), d'où 1 IP publique par front |
| Auth admin | LDAP (sudo, SSH, PAM MariaDB) |

## Déjà en place côté conteneurs
k3s + **ArgoCD en GitOps** (dépôt `argo`, SOPS/age), Traefik, joués en QA le 2026-09-25. Un Dockerfile de dev existe.
→ La brique GitOps sur Kubernetes est acquise ; il reste à y mettre EDEN.

## Ce qu'implique la conteneurisation d'EDEN
| Sujet | Traitement |
|---|---|
| ~22 clients standard | 1 image commune, paramétrée par version + `.env` |
| ~10 clients avec code spécifique | 1 image par client, construite en CI depuis son dépôt (**principal effort**) |
| ~8 clients en PHP 7.4 | Monter de version **avant** la migration |
| Pièces jointes | 1 volume RWO par client (1 pod suffit) |
| Config modifiée à chaud (`config/services.php`) | Passage en variables d'environnement |
| Workers et crons | Deployment `queue:work` + CronJob `schedule:run` |
| TLS et IPs | Traefik + cert-manager : 1 LB, fin du NFS de certificats et des 192 IPs |

## ⚠ Sécurité — à traiter tout de suite
`eden-install-clean.yml` contient **en clair**, et dans l'historique git depuis octobre 2024 (le dépôt est sur GitHub) :
- une clé privée SSH d'accès aux dépôts ;
- la `CLE_CRYPTAGE` d'EDEN ;
- 2 clés API.

À faire : rotation, passage en vault inline, purge de l'historique.
Attention : changer `CLE_CRYPTAGE` peut rendre illisibles des données déjà chiffrées → vérifier côté application avant toute rotation.
