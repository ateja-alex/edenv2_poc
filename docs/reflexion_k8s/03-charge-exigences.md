# 03 — Charge et exigences

## Exigences
| Exigence | Valeur |
|---|---|
| Disponibilité | **99,9 %** (≤ 43 min/mois), avec des clients de plus en plus exigeants |
| RPO | 1 h |
| RTO | 1 jour (perte totale) |
| Usage | Heures ouvrées 8 h–18 h ; mises à jour de nuit sans impact |
| Pics | Fin de mois, vers 11 h (pas de fin de mois dans l'historique disponible) |
| QA | À prévoir dans la cible, même modèle que la PR |

## Charge mesurée (VictoriaMetrics, septembre 2026, hors VMs dev et Windows)
| Périmètre | CPU moy. | CPU p95 | RAM p95 | Disque (données) |
|---|---|---|---|---|
| **PR** : 40 fronts | 3,5 cœurs | 6 cœurs | 40 Go | 761 Go |
| **PR** : 3 MariaDB | 3,5 cœurs | 6 cœurs | 86 Go | ~310 Go |
| **QA** : 52 fronts | 0,8 cœur | 1,4 cœur | 58 Go | 762 Go |
| **QA** : 3 MariaDB | 1,2 cœur | 1,5 cœur | 14 Go | ~120 Go |

- 36 fronts PR sur 40 sont sous 0,35 cœur au p95. Les plus gros : capvisio, sra, mrpompes.
- Le profil journalier est net : CPU ×1,6 entre 9 h et 17 h.
- La RAM de la PR est dominée par les buffer pools MariaDB (3 × 32 Go, fixés par la règle des 70 % de la RAM, **jamais mesurés**).

## Besoin cible
| | RAM | CPU | Stockage provisionné |
|---|---|---|---|
| PR (avec N+1) | ~160 Go utiles → 256 Go de nœuds | 6–12 cœurs | 1,37 To de fichiers + 0,47 To de bases |
| QA (sans N+1) | ~80 Go | 2–3 cœurs | 1,41 To de fichiers + 0,18 To de bases |

Provision = données réelles × 1,5 (marge de croissance), puisque le block est facturé à la taille provisionnée.

## MariaDB : métriques en place (28/09/2026)
- `mysqld_exporter` sur les 3 SQL PR et QA, avec 2 jobs vmagent :
  - activité toutes les 15 s ;
  - tailles de tables toutes les 5 min (collecte de 3 à 21 s, 16k à 50k séries par serveur).
- Dashboard Grafana `mariadb-overview` (« MariaDB - dimensionnement »), en PR et en QA : buffer pool, IOPS, activité, requêtes coûteuses, bases, RAM. Commit `cfae74f`, dépôt `infra/ansible`.
- **Premières valeurs (quelques heures d'historique seulement)** :
  - taux de lecture en mémoire ≥ 99 % sur 01 et 03, 94,6 % au pire sur 02 (buffer probablement en cours de remplissage) ;
  - **01-sql : ~4 500 IOPS au p95** (~6 000 en pic), contre moins de 100 sur 02 et 03 ;
  - plus grosses bases : ema 58 Go, mrpompes 24 Go, sra 13,5 Go.
- **Relecture prévue après ≥ 1 semaine de données** (idéalement avec une fin de mois) → taille des instances MariaDB, nombre d'instances (3 ou 2), taille des volumes.
- Conséquence pour la cible : sur High Speed Gen2, les IOPS augmentent avec la taille du volume (30 IOPS/Go). Le volume de la base 01 doit faire **≥ 250 Go** pour tenir ~7 500 IOPS. Les 470 Go de bases provisionnés (~260 / 55 / 155 Go) suffisent : pas d'impact sur le coût.
