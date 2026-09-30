# syntax=docker/dockerfile:1.7
#
# Image Eden (POC) - deux cibles :
#   app : PHP-FPM + code + vendor (aucun volume requis)
#   web : nginx + assets statiques de public/ (proxy FastCGI vers app)
#
#   docker build --target app -t eden-app .
#   docker build --target web -t eden-web .

ARG PHP_VERSION=8.4

# ---------------------------------------------------------------- base PHP
FROM php:${PHP_VERSION}-fpm-bookworm AS php-base

RUN set -eux; \
    apt-get update; \
    apt-get install -y --no-install-recommends \
        git unzip zip \
        libpng-dev libjpeg-dev libwebp-dev libfreetype6-dev \
        libonig-dev libxml2-dev libzip-dev libicu-dev libgmp-dev; \
    docker-php-ext-configure gd --with-jpeg --with-webp --with-freetype; \
    docker-php-ext-install -j"$(nproc)" pdo_mysql mbstring exif pcntl bcmath gd zip intl soap gmp opcache; \
    apt-get install -y --no-install-recommends $PHPIZE_DEPS; \
    pecl install redis; \
    docker-php-ext-enable redis; \
    apt-get purge -y --auto-remove $PHPIZE_DEPS; \
    rm -rf /var/lib/apt/lists/* /tmp/pear

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# ---------------------------------------------------------------- vendor
FROM php-base AS vendor
WORKDIR /build
ENV COMPOSER_ALLOW_SUPERUSER=1 COMPOSER_NO_INTERACTION=1
COPY composer.json composer.lock ./
RUN --mount=type=cache,target=/root/.composer/cache \
    composer install --no-dev --no-scripts --no-autoloader --prefer-dist

# ---------------------------------------------------------------- app
FROM php-base AS app
WORKDIR /var/www
ENV COMPOSER_ALLOW_SUPERUSER=1 REDIS_CLIENT=phpredis

COPY docker/php/php.ini /usr/local/etc/php/conf.d/zz-eden.ini
COPY docker/php/php-fpm.conf /usr/local/etc/php-fpm.d/zz-eden.conf
COPY docker/php/entrypoint.sh /usr/local/bin/entrypoint.sh

COPY --from=vendor /build/vendor ./vendor
COPY . .

RUN set -eux; \
    chmod +x /usr/local/bin/entrypoint.sh; \
    composer dump-autoload --no-dev --optimize --classmap-authoritative --no-scripts; \
    mkdir -p storage/app/public storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache; \
    php artisan package:discover --ansi; \
    chown -R www-data:www-data storage bootstrap/cache

ENTRYPOINT ["entrypoint.sh"]
CMD ["php-fpm"]
EXPOSE 9000

# ---------------------------------------------------------------- web
FROM nginx:1.27-alpine AS web
ENV PHP_FPM_HOST=app PHP_FPM_PORT=9000 SERVER_NAME=_
COPY docker/nginx/default.conf.template /etc/nginx/templates/default.conf.template
COPY --from=app /var/www/public /var/www/public
EXPOSE 80
