# 06 — Cible OVHcloud et chiffrage

## Décisions (2026-09-28)
- **Variante 1** : nœuds identiques, fronts et MariaDB sur les mêmes nœuds.
- **PR : 4 × r3-64**, N+1 avec de la marge pour ce qui n'est pas encore chiffré (monitoring, services annexes).
- **QA : 2 nœuds, sans SLA.**
- **SLA visé : 99,9 %**, non contractuel (les contrats clients n'en prévoient pas) → MKS Free suffit, Standard en option.
- **Pools SQL** : techniques uniquement → le découpage n'est pas à reprendre tel quel.
- **MySQL managé écarté** (coût + migration MariaDB → MySQL).
- **RWX validé sur le principe** (~+140 €/mois) : 2 pods par client, pour la souplesse de gestion des nœuds. Sous réserve du POC File Storage et des corrections applicatives d'EDEN (« semi-stateful »).

## Architecture cible
**Tout en instances, sur OVH Managed Kubernetes (MKS), région GRA, prix après le 01/10/2026.**

| Brique | Choix |
|---|---|
| Kubernetes | MKS, 1 cluster PR + 1 cluster QA, GitOps ArgoCD (dépôt `argo` existant), secrets SOPS/age |
| Entrée | 1 load balancer Octavia + Traefik + cert-manager → 1 à 2 IPs publiques, TLS automatique |
| EDEN | 1 chart Helm, 1 release par client (`ApplicationSet` alimenté par la base d'inventaire) : Deployment web (nginx + php-fpm), Deployment `queue:work`, CronJob `schedule:run` |
| Images | GitHub Actions → GHCR ; image commune + images dédiées pour les clients avec code spécifique |
| Fichiers | **RWX** : 1 partage OVH File Storage, 1 sous-dossier par client, 2 pods par client (repli : 1 PVC RWO par client en block Classic) |
| MariaDB | `mariadb-operator` dans le cluster, sur les mêmes nœuds que les fronts (variante 1), block High Speed Gen2. Nombre d'instances **libre** (les 3 pools actuels sont techniques) : 1:1 à la migration, regroupement possible ensuite |
| Backup | Dumps MariaDB + Kopia → S3 OVH ; snapshot hebdomadaire des volumes PR ; copie externe S3 Infomaniak |
| Admin | **Pas d'instance VPN dédiée** : API Kubernetes filtrée par IP (fonction MKS), `kubectl port-forward` pour les bases, outils (ArgoCD, Grafana, PMA) derrière l'ingress avec OIDC + liste d'IPs autorisées. Si un VPN reste nécessaire : WireGuard en pod dans le cluster (NodePort UDP, 0 €) ou une d2-2 (~10 €) |

## Chiffrage (€ HT/mois)
| Poste | Détail | Montant |
|---|---|---|
| **PR** nœuds | 4 × r3-64 (8 vCPU / 64 Go garantis, ~217 € avec disque local et IPv4) → 32 vCPU / 256 Go, N+1 | 868 |
| PR stockage | 1 370 Go Classic (0,042) + 470 Go High Speed Gen2 (0,086) | 98 |
| PR snapshots | 1 snapshot hebdomadaire (~1 To utilisé, 0,042) | 45 |
| PR réseau | LB Octavia S + IP flottante | 8 |
| **Sous-total PR** | | **≈ 1 019** |
| **QA** nœuds | 2 nœuds, sans SLA : 1 × r3-64 + 1 × r3-32 (~217 + 110) → 96 Go | 327 |
| QA stockage + LB | 1 410 Go Classic + 180 Go High Speed Gen2 + LB S | 80 |
| **Sous-total QA** | | **≈ 407** |
| **Backup** | S3 OVH ~8 To (0,007) 57 + copie S3 Infomaniak 43 | **≈ 100** |
| **Total** | | **≈ 1 525** |

## Comparaison
| | PR | QA | Backup | **Total** |
|---|---|---|---|---|
| Actuel | 1 265 | 566 | 490 | **≈ 2 321** |
| **Cible OVH** | 1 019 | 407 | 100 | **≈ 1 525 (−34 %, ~9 500 €/an)** |

## Leviers et options
| Levier | Impact/mois |
|---|---|
| MariaDB à ~16 Go au lieu de ~28 Go par instance (buffer pool ~11 Go au lieu de ~22 Go, à mesurer) → 3 × r3-64 en PR | −217 |
| Savings Plan 12 mois sur les nœuds (−15 %) | −180 |
| Marge de provision ×1,2 + agrandissement des volumes à chaud | −35 |
| Rétention des dumps : horaires 48 h, puis quotidiens | S3 ↓ |
| MKS Standard en PR (SLA contractuel 99,9 %) | +66 |
| **Fourchette réaliste** | **≈ 1 250 – 1 610** |

## Scaling horaire des nœuds (jour / nuit)
Prix horaire d'un r3-64 après le 01/10 : ~0,30 €/h. Heures hors usage (hors 7 h–20 h en semaine, et le week-end) : ~447 h/mois.

| Où | Principe | Gain/mois | Contrainte |
|---|---|---|---|
| **PR** : 4 → 3 nœuds la nuit | 3 nœuds fixes (MariaDB épinglé dessus) + 1 nœud de marge retiré la nuit | **~130** | Pas de N+1 la nuit. 2 nœuds la nuit est impossible (128 Go < ~150 Go nécessaires : les fronts gardent leur RAM même inactifs) |
| **QA** : 2 → 1 nœud la nuit | Mise en veille des projets QA (kube-downscaler), le r3-64 est retiré ; le r3-32 garde le socle | **~130** | QA indisponible la nuit et le week-end (démos ?) |
| **Total** | | **~260** | |

- Le cluster-autoscaler réagit aux pods en attente, pas à l'heure → le scaling horaire se fait par un **CronJob qui change la taille du pool via l'API OVH**, ce qui demande un peu d'outillage.
- Il se combine avec un **Savings Plan 12 mois sur les nœuds fixes** (3 r3-64 PR + 1 r3-32 QA, −15 %) : ~−115 €.
- → **EDEN ≈ 1 150 € HT/mois** avec les deux leviers, contre ≈ 1 525 sans.

**Downtime lié au scaling** : retirer un nœud = le vider (drain). Les pods qui s'y trouvent redémarrent ailleurs. Avec 1 pod par client et un volume RWO, chaque client déplacé est **coupé 1 à 3 min** (arrêt du pod, détachement puis rattachement du volume, démarrage). Ajouter un nœud le matin ne coupe rien. MariaDB doit être épinglé sur les nœuds fixes : un déplacement couperait tous les clients de la base.

**Alternative sans downtime planifié : N+1 « à la demande »**
- 3 nœuds r3-64 fixes (192 Go pour ~150 Go), cluster-autoscaler avec 4 nœuds maximum. Il n'y a plus de scaling horaire en PR.
- Si un nœud tombe, ou pendant une montée de version de MKS, les pods en attente déclenchent la création d'un 4e nœud (~5–10 min), qui est supprimé ensuite.
- Gain : **−217 €/mois** (au lieu de −130), et aucune coupure planifiée chaque soir. Contrepartie : une reprise après panne plus lente (délai de création du nœud + redémarrage), compatible avec 99,9 % si les pannes restent rares.
- → **EDEN ≈ 1 065 €** avec la veille nocturne de la QA et un Savings Plan sur les nœuds fixes.

Sources de coupure courte **dans tous les cas** (1 pod par client) : déploiement d'une nouvelle version (`Recreate`), montées de version des nœuds MKS (drain), perte d'un nœud. À faire de nuit.

## Hors stack : Windows (Sage, ERP, AD) et VMs de dev
Il faut les héberger ailleurs, et ce coût doit entrer dans le bilan global.

| Élément | Nb | RAM réelle | CPU | Disque utilisé |
|---|---|---|---|---|
| VMs Windows PR en marche | 8 | ~33 Go | ~1,7 cœur | ~575 Go |
| VMs dev QA | 7 | ~20 Go (p95) | ~0 | ~70 Go |

| Option | Coût/mois | Commentaire |
|---|---|---|
| **1 serveur dédié PVE autonome** (réutiliser un ADVANCE-1 QA actuel : 6c/12t, 128 Go, NVMe) + backup vzdump/PBS vers S3 | **~205** | Licences Windows inchangées (à vérifier) ; pas de HA : si le serveur tombe, restauration (RTO ≤ 1 j, conforme) |
| 2 serveurs PVE avec réplication ZFS | ~390 | HA « manuelle » en quelques minutes |
| Instances Public Cloud Windows | **≥ 1 000** | **Licence Windows OVH : 0,0347 €/vCore/h ≈ 25 €/vCore/mois** → hors de prix |
| Dev en pods dans la QA k8s | +110 (1 r3-32) | Alternative pour les VMs de dev |

## Bilan global (EDEN + hors stack)
Décision : Windows et dev sur **1 serveur dédié** (plus simple), **budget ≤ 300 €/mois** (~205 € en réutilisant un ADVANCE-1 de la QA actuelle).

| | EDEN | Hors stack | **Total** | vs actuel (2 321) |
|---|---|---|---|---|
| Base | 1 525 | 300 | **≈ 1 825** | −21 % (~6 000 €/an) |
| + scaling jour/nuit + Savings Plan 12 mois | 1 150 | 300 | **≈ 1 450** | −38 % (~10 500 €/an) |
| + N+1 à la demande (3 nœuds fixes) + veille QA + Savings Plan | 1 065 | 300 | **≈ 1 365** | −41 % (~11 500 €/an) |
| + MariaDB redimensionné (−1 nœud PR) | ~960 | 300 | ≈ 1 260 | −46 % |
| *Rappel : garder PVE en l'optimisant (IPs, Glacier)* | | | *≈ 2 030* | *−13 %* |

À noter :
- Les 2 321 € actuels incluent déjà l'hébergement Windows et dev : la comparaison se fait bien à périmètre égal.
- **Coût ponctuel de migration** : 1 à 3 mois de double infra (~1 500 €/mois) → amorti en 4 à 9 mois selon les leviers activés.
- Le gain principal n'est pas que financier : plus de Ceph, de cluster PVE à 6 nœuds, de 40 OS clients, d'IPs par front ni de déploiements manuels (objectif : ~50 h → ~20–25 h/mois).

## Stockage partagé (RWX) chez OVH
Objectif : **2 pods par client** → déploiements, drains et pertes de nœud sans coupure, et scaling des nœuds sans downtime.
Prix du catalogue API OVH (FR, HT), relevés le 2026-09-28.

| Option | Principe | Prix | Limites connues | Verdict |
|---|---|---|---|---|
| **Public Cloud File Storage** (nouveau, GA) | NFS managé (Manila), **RWX natif dans MKS** via CSI | **0,142 €/Gio/mois** ; 1 partage de 1,4 Tio ≈ **200 €** | 150 Gio min à 10 Tio max par partage ; 24 IOPS/Go (16 000 max) ; débit 0,25 Mo/s/Go, **plafonné à 128 Mo/s par partage** ; prix affiché « free » dans le catalogue (lancement ?) | **Candidat n°1**, à valider en POC |
| Enterprise File Storage (NetApp) | NFS NetApp, via le vRack | 125 €/To/mois, 1 To min ; 2 To ≈ **250 €** | Produit hors Public Cloud (vRack Services) | Solide mais plus cher |
| NAS-HA SSD | NFS historique | 3 To = **159 €** (PR + QA ensemble) | Gamme ancienne (risque de fin de commercialisation), accès par ACL d'IP | Le moins cher, mais le moins pérenne |
| Block multi-attach (`classic-multiattach`) | Même volume attaché à plusieurs nœuds | 0,042 €/Go | Exige un système de fichiers cluster (OCFS2/GFS2) : pas un RWX k8s | ❌ |
| NFS / Longhorn / Rook-CephFS gérés par nous | Stockage partagé auto-hébergé | coût des nœuds | Point unique de panne ou complexité : le Ceph qu'on veut quitter | ❌ (bus factor) |

**Découpage** : 1 partage (ou 1 par groupe de clients) avec 1 sous-dossier par client, et non 1 partage par client (150 Gio minimum × 40 = ~850 €).

**Ce que ça change**
| | RWO (actuel) | RWX File Storage |
|---|---|---|
| Stockage fichiers PR | 58 € (block Classic) | ~200 € |
| RAM des 2es réplicas (~0,7 Go × 40) | — | ~28 Go, tient dans 3 × r3-64 (N+1 à la demande) |
| Coupure au déploiement / drain / perte de nœud | 1 à 3 min par client | **aucune** (l'autre réplica sert) |
| Scaling jour/nuit des nœuds | coupe les clients déplacés | **sans coupure** |
| Nombre de volumes attachés par nœud | 1 par client (limite par nœud à vérifier) | 1 montage NFS par pod, plus de limite d'attachement |
| Point unique de panne | non (1 volume par client) | **oui : le partage** (en panne = tous les clients sans pièces jointes) |
| Surcoût net PR | — | **~+140 €/mois** |

**Prérequis applicatifs** pour 2 réplicas : sessions et cache partagés (Redis ou base, pas de fichiers locaux), verrous des queues et du scheduler (`onOneServer`), code uniquement dans l'image. La QA peut rester en RWO.

**À valider en POC** : latence NFS sur les uploads/téléchargements, plafond de 128 Mo/s (le trafic web PR culmine aujourd'hui à ~140 Mb/s, soit ~18 Mo/s), comportement du CSI Manila dans MKS, tarif réel (mention « free »), et limite de volumes attachés par nœud en RWO (plan de repli).

## Variantes de placement de MariaDB (PR)
Besoin : fronts + infra ~50 Go / 6–8 cœurs au p95 (pics à ~20) ; 3 MariaDB de ~28 Go chacun (VM de 32 Go aujourd'hui, buffer pool = 70 % ≈ 22 Go).
Prix des nœuds après le 01/10 : r3-32 110 €, b3-32 173 €, r3-64 217 €. Stockage, QA et backup identiques dans les trois cas (+658 €).

| | 1. Nœuds identiques, fronts et SQL mélangés | 2. Pool fronts + pool SQL | 3. Fronts k8s + SQL sur instances à part |
|---|---|---|---|
| Nœuds PR | 4 × r3-64 (N+1) | 3 × b3-32 + 3 × r3-64 | 3 × b3-32 + 3 × r3-32 (1 par pool) |
| Coût nœuds PR | **868** | 1 170 (981 avec des fronts en r3-32) | **849** |
| **Total** | **≈ 1 525** | ≈ 1 825 (1 640) | **≈ 1 505** |
| Si un nœud / une instance SQL tombe | le pod redémarre seul sur un autre nœud (volume réattaché, quelques min) | idem | **pas de bascule automatique** : redémarrage de l'instance, sinon restauration |
| HA SQL « comme aujourd'hui » (PVE HA) | oui, native | oui, native | non ; avec un réplica par base → +330 (≈ 1 835) |
| Isolation front ↔ SQL | via requests/limits, priorités, anti-affinité | **forte** (taints) | **totale** |
| Réserve N+1 payée | 1 seule, mutualisée | 2 (une par pool) | aucune côté SQL |
| Exploitation | 1 plateforme, tout en GitOps | 1 plateforme, 2 pools | k8s + 3 VMs Debian à patcher (Ansible `sql-install`) |
| Perf SQL | block HS Gen2 | block HS Gen2 | block HS Gen2, **ou NVMe local de l'instance** (plus rapide, mais perdu si l'hôte meurt) |

Lecture :
- **Le cas 1 est le meilleur compromis** : même prix que le cas 3, avec la bascule automatique de SQL et une seule plateforme. Son risque (des fronts qui gênent SQL) se traite par la configuration (requests garanties, PriorityClass, anti-affinité entre les 3 MariaDB).
- **Le cas 2 coûte ~300 € de plus** parce que chaque pool paie sa propre réserve N+1. Il se justifie si l'on constate des interférences, ou à plus grande échelle.
- **Le cas 3 n'est moins cher que parce qu'il renonce à la HA de SQL.** Il réintroduit des serveurs à gérer, et à HA égale c'est le plus cher. Il n'a d'intérêt que si le POC montre que le block réseau ne suffit pas (option NVMe local + réplica).

**Cas 4 — MySQL managé OVH** (catalogue API, GRA, prix par nœud ; le plan Production impose 2 nœuds, Essential 1 nœud sans HA) :
| Variante | Base | Coût SQL | **Total** (fronts 3 × b3-32, QA avec un MySQL Essential b3-16) |
|---|---|---|---|
| Iso aujourd'hui, HA | 3 × Production b3-32 (8 vCPU / 32 Go × 2 nœuds) | 3 908 | **≈ 5 330** |
| Regroupé, HA | 1 × Production b3-64 (× 2 nœuds) | 2 605 | ≈ 4 030 |
| Regroupé serré, HA | 1 × Production b3-32 (× 2 nœuds) | 1 302 | ≈ 2 730 |
| Sans HA | 3 × Essential b3-32 | 1 718 | ≈ 3 140 |
→ **Écarté** : toutes les variantes dépassent le coût actuel (2 321), et elles imposent une **migration MariaDB → MySQL** (collations, syntaxe propre à MariaDB, fin de l'auth PAM/LDAP) que l'utilisateur considère déjà comme un frein.

Autres pistes :
- **Réplication MariaDB dans k8s** (opérateur : réplication async ou Galera) : bascule en quelques secondes au lieu de quelques minutes, pour ~+96 Go (+2 × r3-64). Utile seulement si 99,9 % ne suffit plus.
- **Regrouper les 3 MariaDB** : les pools (1 SQL par groupe de ~20 clients) venaient des VMs et des VLANs ; en k8s, 1 ou 2 instances plus grosses mutualisent mieux la RAM, au prix d'un impact plus large en cas de panne.
- **Paris 3-AZ** (MKS Standard, 99,99 %) : les volumes restent dans leur zone, donc la tolérance à la perte d'une zone passe par la réplication SQL. C'est surdimensionné pour 99,9 %.

## Incertitudes à lever (POC)
1. **Perf de MariaDB** sur High Speed Gen2 (30 IOPS/Go, jusqu'à 20 000 IOPS) face au Ceph NVMe actuel.
2. **Taille réelle des buffer pools** (`mysqld_exporter` : ratio de lectures disque et pages libres).
3. **Temps de réattachement d'un volume** quand un nœud tombe (budget de 43 min/mois).
4. **Prix exacts après le 01/10** (disque local des nœuds MKS : réductible ou non), à confirmer au calculateur OVH.
5. **Volume de backup** réel (8 To supposés).
