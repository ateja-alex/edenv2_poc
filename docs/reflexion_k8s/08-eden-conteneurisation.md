# 08 — EDEN : ce qu'il faut corriger pour Kubernetes (2 pods par client)

Audit du code en lecture seule, le 2026-09-28 : `std-eden` (commit `38403c5`), cœur `app/Eden` (`b3cdf6d51`), `public/eden`.
Conclusion : **en l'état, EDEN ne peut pas tourner en plusieurs pods.** Les corrections sont surtout de configuration et de périmètre (effort S/M), sauf **un point structurant : le PHP généré à l'exécution**.

## Points bloquants
| Sujet | Constat | Correctif | Effort |
|---|---|---|---|
| **Sessions et cache** | `file` (locaux au pod) ; invalidation du cache (`eden.version`), `Cache::lock` des synchros et `queue:restart` ne valent que dans un pod ; panier e-commerce lié à la session | Redis : `SESSION_DRIVER` / `CACHE_STORE=redis`, `REDIS_CLIENT=phpredis`, préfixe et base propres à chaque client, sessions séparées du cache (`Cache::flush` = `FLUSHDB`) | S |
| **PHP généré à l'exécution (no-code)** | Le paramétrage écrit des fichiers PHP puis les inclut : `storage/app/eden_*.php` (fonctionnalités, fiches, familles, menus, intranet, CSS) et **`app/Migrations/`**, donc dans le code. Écritures non atomiques. `config:cache` interdit | Court terme : `storage/app` **et** `app/Migrations` sur le volume RWX, écriture atomique (fichier temporaire + `rename`), `opcache.validate_timestamps=1`. Cible : stockage en base | M, puis L |
| **`composer update` à l'exécution** | `mise_a_jour_composer()` réécrit `composer.json` et `vendor/` ; route `migrations_installation` **sans authentification** | Composer au build de l'image ; migrations lancées par un Job unique (hook Helm) ; route protégée | M |
| **Crons** | 43 routes GET **sans authentification**, appelées par un cron externe ; verrou `en_cours` non atomique, jamais libéré si le pod est tué | CronJobs k8s (`concurrencyPolicy: Forbid`) vers le Service interne, verrou atomique (`UPDATE … WHERE en_cours=0` ou `Cache::lock` Redis), `/eden/cron` bloqué sur l'ingress | M |
| **Queues** | Aucun worker défini ; exports chaînés qui écrivent sur disque | Deployment `queue:work` avec le même montage RWX | S |
| **Derrière l'ingress** | `TrustProxies` vide ; `$_SERVER['HTTPS']` testé en dur, avec `dd()` → la génération des composants plante si le TLS est terminé en amont | `$proxies` sur le réseau du cluster, `fastcgi_param HTTPS on` / `request()->secure()` | S |
| **Image** | Le Dockerfile actuel est un environnement de dev (bind mount, composer à l'exécution, Xdebug, `display_errors`) | Image de production multi-stage, utilisateur non root, système de fichiers racine en lecture seule | M |

## À corriger (non bloquant)
- **Écritures hors du volume** : `public/tmp`, un fichier en chemin relatif (Geodis), des `makeDirectory` relatifs qui créent `public/public/...` → passer par `storage_path()` ou `sys_get_temp_dir()`.
- **Code et config réécrits** : `config/app.php` par une migration, des fichiers de code et des traductions PHP réécrits par la maintenance → à neutraliser en production.
- **Nettoyage des mails reçus** : `rename` du dossier `email_recus` entier, avec risque de perte en multi-pod → supprimer fichier par fichier.
- **Jeton Google Drive** dans un fichier → en base (ou sur le volume RWX).
- **Clés API** injectées en éditant `config/services.php` → `env()` + Secret k8s.
- **Ghostscript** appelé (`gs`) mais absent de l'image, erreur silencieuse → à ajouter.
- **Logs** en fichiers quotidiens (un `fix_fk.log` de 1,2 Go en local), avec une visionneuse dans l'application → `LOG_CHANNEL=stderr` + collecte centralisée.
- **Divers** :
  - `git` exécuté par l'application → version passée en variable de build ;
  - symlink `public/storage` absolu → relatif, créé au build ;
  - 127 appels `env()` hors de `config/` (mineur tant que la config n'est pas mise en cache).

## Volumes
| Persistant, partagé entre pods (RWX, 1 sous-dossier par client) | Éphémère (`emptyDir` ou image) |
|---|---|
| `storage/app/` en entier : paramétrage `eden_*.php`, pièces jointes, `email_recus`, exports, composants JS/CSS générés, SEPA, transporteurs, jeton Google | `/tmp`, `storage/framework/*`, `storage/logs` |
| `app/Migrations/` (tant que le PHP généré n'est pas passé en base) | `bootstrap/cache` (au build), tout le code et `vendor/` (image, lecture seule) |
| Monté dans : pods PHP, workers, CronJobs **et nginx** (qui sert `/storage`) | |

Hors fichiers : MariaDB (y compris la table `jobs`) et **Redis** (sessions et cache, isolés par client). Les sessions sont volumineuses, car le cache métier y est stocké.

## ⚠ Sécurité (vaut aussi pour la prod actuelle)
- **Routes sans authentification** : `migrations_installation` (déclenche `composer update`) et les 43 routes `cron`. Il faut au minimum les filtrer par IP sur les VMs actuelles.
- **Secrets versionnés dans le code** : clés Google Places et Maps, clé Stripe (test), un mot de passe en dur (`Maintenance_management.php`). Les mots de passe d'intégration sont en base64 dans `storage/app/eden_fonctionnalites.php` → sortir des sources et faire une rotation.

## Effort
- Environ 14 corrections S, 7 M et 1 L (le PHP généré stocké en base).
- Estimation grossière : **~6 à 9 semaines-développeur**, hors passage du PHP généré en base.
- La plupart des corrections améliorent aussi la prod actuelle (sécurité, logs, crons) et peuvent démarrer avant la migration.

## Décision à prendre
**Le code généré par le paramétrage (`app/Migrations`, `storage/app/eden_*.php`)** :
- (a) le garder en fichiers sur le volume partagé → rapide, mais du code exécutable vit hors de l'image ;
- (b) le figer dans l'image du client (commit dans son dépôt, build CI) → propre, mais le paramétrage n'est plus « live » en prod ;
- (c) le stocker en base → la cible, mais c'est le chantier le plus lourd.
