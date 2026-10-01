# Plan Claude Code — dashboard Grafana MariaDB (dépôt `eden/ansible`)

> À donner à Claude Code lancé dans `/home/alex/repos/infra/ansible`.
> Exemple : `claude "Exécute le plan /home/alex/temp/51_reflexion_k8s_cloud/plans/claude-dashboard-mariadb.md"`

## Contexte
- `mysqld_exporter` est déployé sur les 3 SQL en PR et en QA (28/09/2026). Il y a 2 jobs vmagent :
  - `mysqld_exporter` (15 s) : `global_status`, `global_variables`, `perf_schema.eventsstatements`… ;
  - `mysqld_exporter_tables` (5 min) : `info_schema.tables`.
- VictoriaMetrics : PR `http://metrics.ateja.corp:8428`, QA `http://metrics.qa.ateja.corp:8428` (1 collecteur et 1 Grafana par environnement).
- Objectif du dashboard : **produire les chiffres pour dimensionner MariaDB** dans la cible Kubernetes (taille du buffer pool, IOPS, regroupement des bases, taille des volumes). Ensuite, optimiser EDEN.

## Règles du dépôt (CLAUDE.md, à respecter)
- **Vérifier avant d'affirmer** : chaque requête PromQL est jouée contre VictoriaMetrics (PR **et** QA) avant d'entrer dans le JSON. Un panel dont la métrique n'existe pas est retiré, pas laissé vide.
- **Spec dans le même commit** : `specs/monitoring.md` (§6 « Dashboards maison » : ligne du tableau + « Pourquoi », §1 composants si besoin, §11 limites, §12 décisions).
- Commentaires sans accents dans les fichiers de rôle ; specs accentuées. Commits `type : module : sujet`.
- **Pas de push, pas de déploiement** : je relis, je déploie.

## Étape 0 — Pré-requis
1. Vérifier que le rôle `mysqld_exporter` et les 2 jobs vmagent sont bien dans le dépôt, sur la branche courante. Au 28/09, ils ne sont pas visibles sur `master`. Sinon, **s'arrêter et me le signaler**.
2. Vérifier que `02-sql.qa.ateja.corp` apparaît dans `up{job="mysqld_exporter_tables"}` en QA (il manquait juste après la mise en place).
3. Créer la branche `feat/monitoring-mariadb`.

## Étape 1 — Inventaire des métriques
Relever, en PR et en QA, la présence et les labels de :
`mysql_up`, `mysql_global_variables_innodb_buffer_pool_size`, `mysql_global_status_innodb_buffer_pool_bytes_data`, `mysql_global_status_buffer_pool_pages{state}`, `mysql_global_status_innodb_buffer_pool_reads`, `..._read_requests`, `mysql_global_status_innodb_data_reads|writes|read|written|fsyncs`, `mysql_global_status_queries`, `mysql_global_status_commands_total{command}`, `mysql_global_status_threads_connected|running`, `mysql_global_status_slow_queries`, `mysql_global_status_aborted_connects`, `mysql_global_status_created_tmp_disk_tables`, `mysql_global_status_sort_merge_passes`, `mysql_global_status_table_open_cache_misses`, `mysql_perf_schema_events_statements_*`, `mysql_info_schema_table_size{schema,table,component}`, `mysql_info_schema_table_rows`, et côté node_exporter `node_disk_*` des hôtes `host_type="sql"`.
→ Remplacer les noms absents par leur équivalent réel, ou retirer le panel. Noter les écarts pour la spec.

## Étape 2 — Dashboard `dashboard_mariadb.json`
Fichier : `roles/monitoring/grafana/files/dashboard_mariadb.json`, uid `mariadb-overview`, datasource `${DS_PROMETHEUS}` (remplacée par le rôle).
Variables :
- `$instance` = `label_values(mysql_up{job="mysqld_exporter"}, instance)`, multi-valeur + All ;
- `$schema` = `label_values(mysql_info_schema_table_size{instance=~"$instance"}, schema)`.
**Toujours filtrer sur `job`** (`mysqld_exporter` pour l'activité, `mysqld_exporter_tables` pour les tailles) : les métriques communes existent en double.

| Ligne | Panels |
|---|---|
| **1. Dimensionnement** | Taux de lecture en mémoire par instance (`1 - rate(reads) / rate(read_requests)`), seuils 99 % / 98 %) ; buffer pool : taille / données / libre (Go) ; lectures disque InnoDB/s ; stat « pire taux de lecture en mémoire » sur la période ; stat « données en cache / taille du pool » |
| **2. Disque (IOPS)** | IOPS InnoDB lecture et écriture ; débit Mo/s ; fsync/s ; IOPS et latence du disque de la VM (node_exporter, `host_type="sql"`) ; stats p95 et p99 des IOPS (`quantile_over_time`) sur la période |
| **3. Activité** | Requêtes/s par instance ; commandes/s par type (select, insert, update, delete) ; threads connectés et actifs ; requêtes lentes/s ; connexions refusées |
| **4. Requêtes coûteuses** | Table top 20 par temps (`topk(20, rate(..._seconds_total[$__range]))`), colonnes `schema`, `digest_text`, appels/s, temps moyen, lignes examinées / lignes renvoyées ; filtre `$schema` |
| **5. Bases clients** | Top 20 des bases par taille (données + index) ; total par instance ; croissance sur 7 et 30 jours (`delta`) ; nombre de lignes |
| **6. Réglages** | Tables temporaires sur disque/s ; `sort_merge_passes`/s ; échecs du cache de tables/s ; RAM de MariaDB comparée à la RAM de la VM (node_exporter) |

Suivre le style des dashboards existants (`dashboard_infra.json`) :
- couleurs de série fixes, couleurs de statut réservées aux seuils ;
- description sur chaque panel qui a un piège (ex. : double job, `$__range` pour les tops).

## Étape 3 — Déclaration
Ajouter dans `monitoring.yml` (`grafana_dashboards`), avec un commentaire sans accents, sur le modèle des autres :
```yaml
      - label: "mariadb"
        json: "mariadb.json"
        datasource: "VictoriaMetrics"
        mode: "local"
```
Valider la syntaxe du JSON (`python3 -m json.tool`) et rendre le playbook en `--syntax-check`.

## Étape 4 — Spec
`specs/monitoring.md` :
- date en tête ;
- ligne `dashboard_mariadb.json` dans le tableau des dashboards maison ;
- paragraphe « Pourquoi un dashboard MariaDB séparé » (namespace `mysql_*`, double job, cardinalité du job tables) ;
- limites connues (métriques absentes relevées à l'étape 1, 02-sql.qa si toujours absent) ;
- décision : « job tables à 5 min : collecte de 3 à 14 s et ~45 000 séries par serveur ».

## Étape 5 (optionnelle, à me proposer avant de la faire) — Alertes vmalert
3 règles, testées par `vmalert-tool unittest` comme les 7 existantes :
- `MariaDBDown` : `mysql_up{job="mysqld_exporter"} == 0` pendant 2 min ;
- `MariaDBBufferPoolHitRatioLow` : taux de lecture en mémoire < 98 % pendant 15 min ;
- `MariaDBSlowQueriesSpike` : requêtes lentes/s > 3 × la moyenne sur 1 j.

## Livrable attendu
- 1 commit `feat : monitoring : dashboard mariadb` (dashboard + `monitoring.yml` + spec), **non poussé**.
- Un compte rendu :
  - requêtes vérifiées (PR / QA) ;
  - panels retirés et pourquoi ;
  - premiers chiffres lus (taux de lecture en mémoire, IOPS p95, top 5 des bases) ;
  - commande de déploiement à jouer :
    ```bash
    ansible-playbook monitoring.yml -e eden_env=qa|pr --tags <tag grafana> --ask-vault-pass
    ```
