# 01 — Existant

Relevé du 2026-09-28 : inventaire PVE, VictoriaMetrics, factures OVH (septembre 2026) et AWS (août 2026).

## Infrastructure
| Élément | Détail |
|---|---|
| PR | Cluster PVE 9.2 `ATEJA-C-01` : 3 × OVH ADVANCE-4 (EPYC 4584PX 16c/32t, 128 Go, 2 × 1,92 To NVMe pour Ceph) |
| QA | 3 × OVH ADVANCE-1 (EPYC 4244P, 128 Go, mêmes NVMe) |
| Stockage | Ceph réplica 3 sur NVMe, 10 Tio bruts, **51 % utilisés** |
| Réseau | vRack 25 G (partagé avec Ceph), full VLAN, OPNsense par pool, WireGuard entre les sites (QA, PR, Siège) et pour les utilisateurs |
| DNS | Gandi (externe), PowerDNS (interne), tous deux gérés à la main |
| Backup | PBS sur ADVANCE-STOR (4 × 22 To) + copie S3 Infomaniak ; dumps MariaDB horaires et Kopia (fichiers, toutes les 30 min) vers S3 AWS |
| Monitoring | VictoriaMetrics + Grafana (PR et QA), mis en place en septembre 2026 |

## Guests PR (73 au total, 65 en marche)
| Catégorie | Nb | Alloué | Réellement utilisé |
|---|---|---|---|
| Fronts EDEN (1 VM par client) | 40 | 320 vCPU / 240 Go | ~4 cœurs / ~40 Go |
| MariaDB (1 par pool) | 3 | 48 vCPU / 96 Go | ~4 cœurs / ~85 Go (buffer pool) |
| Windows (Sage, ERP, AD) | 9 | — | **sortent du périmètre** |
| OPNsense, infra (DNS, LDAP, NFS, cron, k3s services, monitoring) | 13 | — | faible |

RAM réelle totale : ~150 Go (Grafana, hors cache), contre ~300 Go vus par PVE (cache et ballooning compris). Le CPU est alloué environ 40 fois plus qu'il n'est consommé.

## Coûts (€ HT/mois)
| Poste | Montant |
|---|---|
| PR : 3 serveurs + options | 914 |
| QA : 3 serveurs + options | 566 |
| IPs publiques : 2 × /26 + 2 × /27 (192 IPs, ~1 par front) | 351 |
| PBS | 284 |
| S3 Infomaniak | 43 |
| AWS (S3 + Glacier + trafic sortant) | ~163 |
| **Total** | **≈ 2 321** |

Engagement OVH : aucun, résiliable au mois.

## Constats (à traiter quel que soit le scénario)
1. **Ceph n'est pas N+1 au niveau disque** : si un NVMe meurt, l'autre OSD du même hôte passe à ~100 % et les écritures sont bloquées.
2. **10 VMs en Debian 11**, dont le support LTS est terminé depuis le 31/08/2026 → migration Debian 12 prévue.
3. **Le trafic front → SQL passe par l'OPNsense du pool** (~1,3 Gb/s sur 2 vCPU) : goulot possible à moyen terme.
4. **AWS : ~40 $/mois de suppression anticipée Glacier** (des objets expirent avant les 180 jours minimum facturés). Les dumps SQL sont complets toutes les heures (~11 To/mois envoyés).
5. **IPs : 17 % de la facture**. Un reverse proxy ou un ingress les ramène à 1 ou 2 adresses.
6. **Temps d'admin ~50 h/mois**, assuré par une seule personne.

Données brutes : `annexes/inventaire-vms.csv`, `annexes/inventaire-pve-20260928.tar.gz` (script `annexes/inventaire-pve.sh`).
