#!/bin/sh
set -e

export FPM_MAX_CHILDREN="${FPM_MAX_CHILDREN:-16}"

cd /var/www

# Lien public/storage -> storage/app/public (cible relative a l'image)
[ -e public/storage ] || [ -L public/storage ] || php artisan storage:link

exec "$@"
