FROM php:8.4-fpm-bookworm

# Arguments définis dans docker-compose.yml
ARG UID=1000
ARG GID=1000

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libjpeg-dev \
    libwebp-dev \
    libfreetype6-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libzip-dev \
    libicu-dev \
    libc-client-dev \
    libkrb5-dev


# Modification de l'utilisateur www-data pour correspondre à l'hôte
RUN groupmod -g ${GID} www-data \
    && usermod -u ${UID} -g www-data www-data


# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-configure gd --with-jpeg --with-webp --with-freetype \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip intl soap

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Copy PHP and PHP-FPM configuration overrides (timeouts 3600s)
COPY php/php.ini /usr/local/etc/php/conf.d/zz-timeouts.ini
COPY php/php-fpm.d/www.conf /usr/local/etc/php-fpm.d/zz-www-timeouts.conf

# Install Xdebug (v3) and Redis extension — Xdebug installé mais désactivé (réactiver : docker-php-ext-enable xdebug redis)
RUN set -eux; \
    apt-get update; \
    apt-get install -y --no-install-recommends $PHPIZE_DEPS; \
    pecl install xdebug redis; \
    docker-php-ext-enable redis; \
    apt-get purge -y --auto-remove $PHPIZE_DEPS; \
    rm -rf /var/lib/apt/lists/* /tmp/pear

# Xdebug configuration (env-driven)
COPY php/conf.d/xdebug.ini /usr/local/etc/php/conf.d/zz-xdebug.ini

# Entrypoint: runs composer install if vendor/ is missing, then starts php-fpm
COPY php/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["php-fpm"]
