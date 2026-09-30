#!/bin/sh
set -e

cd /var/www

if [ ! -d "vendor" ] || [ ! -f "vendor/autoload.php" ]; then
    echo "[entrypoint] Running composer install..."
    su -s /bin/sh www-data -c "composer install --no-interaction --prefer-dist --optimize-autoloader"
else
    echo "[entrypoint] vendor/ already present, skipping composer install"
fi

if [ ! -d "public/eden" ]; then
    su -s /bin/sh www-data -c "php artisan storage:link"
else
    echo "[entrypoint] public/eden already present, skipping storage:link"
fi
exec "$@"
