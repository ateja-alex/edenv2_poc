# 05 — Choix du fournisseur : OVHcloud

Comparaison faite le 2026-09-28 sur les catalogues publics (API OVH et Scaleway, données tarifaires d'Infomaniak) et sur la documentation officielle des trois fournisseurs.
Critère de comparaison : **instances à gabarit équivalent** (x86, vCPU garantis ou dédiés), stockage block, Kubernetes managé.

## Décision
| Fournisseur | Statut | En une phrase |
|---|---|---|
| **OVHcloud** | **Retenu** | Le moins cher à gabarit équivalent, le meilleur SLA contractuel, et la continuité du vRack actuel |
| Scaleway | Plan B | Produit solide, mais plus cher dans tous les cas chiffrés |
| Infomaniak | Écarté pour la prod | Pas assez mûr pour faire tourner MariaDB et EDEN en production |

## Pourquoi OVHcloud
- **Prix** : à gabarit x86 garanti, 10 à 35 % moins cher que Scaleway (r3-64, 8 vCPU / 64 Go : ~217 € contre 301 € pour la POP2-HM de Scaleway). Le block Classic est 2 fois moins cher (0,042 contre 0,095 €/Go).
- **SLA** : instances à 99,99 % (vCPU « garantis » sur b3/c3/r3) ; Kubernetes managé (MKS) Standard à 99,9 % (1-AZ) ou 99,99 % (3-AZ).
- **Standard** : OpenStack, provider Terraform officiel actif, Kubernetes managé depuis 2019.
- **Migration** : MKS se branche sur **le vRack existant** → bascule client par client, sans big bang ni rupture réseau.
- **Conformité** : France (GRA, RBX, SBG, Paris 3-AZ), ISO 27001, HDS, SecNumCloud (SNC Cloud Platform, septembre 2026).

Points de vigilance :
- **Hausse de prix au 01/10/2026** : le disque local et l'IPv4 des instances sont désormais facturés à part (+12 à +20 %). Elle est **intégrée** dans le chiffrage.
- Les versions de Kubernetes arrivent jusqu'à 3 mois après l'upstream. MKS ne donne pas d'accès SSH aux nœuds et ne permet pas de les redimensionner.
- La documentation est parfois incohérente sur les plafonds IOPS et débit → à mesurer dans le POC.
- Le catalogue évolue : anciennes gammes encore listées, changements tarifaires.

## Pourquoi Scaleway est en plan B
- **Plus cher dans tous les cas chiffrés** : ~+270 €/mois avec des fronts en ARM, ~+600 €/mois en x86 dédié. Même avec la remise d'engagement maximale (jusqu'à −25 %) face à la remise OVH à 12 mois (−15 %), OVH reste ~130 € moins cher.
- **Pour être compétitif, il impose des concessions** :
  - fronts en **ARM** (gamme BASIC2) → images multi-arch à construire ;
  - SLA des instances de 99 % (BASIC) à 99,5 % (dédiés), **aucun SLA** sur PRO2/PLAY2 ;
  - block 2 fois plus cher.
- Changement de fournisseur → pas de continuité réseau pendant la migration.
- Ses points forts réels : versions de Kubernetes les plus à jour, cycle de vie des gammes formalisé.
→ **À réactiver si le POC OVH échoue** (perf MariaDB, stabilité de MKS). L'architecture cible (images, Helm, ArgoCD) reste portable.

## Pourquoi Infomaniak est écarté
Malgré des prix 3 à 5 fois plus bas, **Infomaniak ne répond pas aux besoins de la production** :
1. **Stockage trop lent pour MariaDB** : le block par défaut (perf1) plafonne à **500 IOPS par volume**, et perf2 à 1 000 IOPS, disponible seulement sur demande au support. OVH High Speed Gen2 monte jusqu'à 20 000 IOPS.
2. **Kubernetes trop jeune** : service lancé en **avril 2025**. Un retour public de janvier 2026 le décrit comme « unsuitable for production » (une seule version disponible, provisioning des nœuds instable). Le CSI et les snapshots de volumes ne sont pas documentés.
3. **Instances opaques** : une seule gamme ; vCPU partagés ou dédiés, overcommit : **non documentés**. Le matériel constaté est de génération 2019 (AMD EPYC Rome). On ne sait pas ce qu'on achète, donc on ne peut pas comparer à gabarit égal.
4. **Hors UE** : datacenters uniquement en Suisse (Genève). Le RGPD est couvert par la décision d'adéquation, mais la France est privilégiée. Pas de HDS ni de SecNumCloud.
5. **Outillage récent** : provider Terraform officiel depuis 2025.

→ **Conservé uniquement pour la copie externe des backups** (S3, déjà utilisé, 43 €/mois) : le stockage objet n'a pas ces limites, et cela met les sauvegardes hors d'OVH.
