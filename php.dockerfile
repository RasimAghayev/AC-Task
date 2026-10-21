# The application image of AzeriCard.
#
# The base image is pinned by digest: a floating tag would let the runtime
# drift away from the version the lock file was built for. The source is
# copied into the image, so the container needs no bind mount of the
# repository. Every bind mount of the compose stack lives under var/ (R92).

FROM php:8.3-fpm@sha256:411e3bf3e51d20c43ff5de62ec82baafcd7499f0beab2df5bb7f671ba730867e AS base

RUN apt-get update && apt-get install -y --no-install-recommends \
        libpng-dev \
        libpq-dev \
        libonig-dev \
        libxml2-dev \
        libzip-dev \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        curl \
        unzip \
        git \
    && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
    && docker-php-ext-configure pgsql -with-pgsql=/usr/local/pgsql \
    && docker-php-ext-install pdo pdo_mysql mysqli mbstring exif pcntl bcmath gd zip pdo_pgsql \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html/be

# The dependency layer. The lock file alone decides the tree, so the layer is
# reused whenever only the application code changes.
FROM base AS vendor

COPY src/be/composer.json src/be/composer.lock ./
RUN composer install --no-interaction --no-progress --no-scripts --prefer-dist

# The test image carries the suite next to the application. The suite runs
# against a database in memory, so it needs neither a database nor a
# network.
FROM base AS test

COPY --from=vendor /var/www/html/be/vendor /var/www/html/be/vendor
COPY src/be/ /var/www/html/be/
RUN composer dump-autoload --optimize --no-interaction --no-scripts \
    && chown -R www-data:www-data /var/www/html/be

USER www-data

ENTRYPOINT ["/var/www/html/be/vendor/bin/phpunit"]

# The runtime image carries the application only. The process runs as the
# unprivileged www-data user, and the image holds no secret.
FROM base AS runtime

COPY --from=vendor /var/www/html/be/vendor /var/www/html/be/vendor
COPY src/be/ /var/www/html/be/
RUN composer dump-autoload --optimize --no-interaction --no-scripts \
    && php artisan package:discover --ansi \
    && mkdir -p storage/framework/{cache/data,sessions,testing,views} storage/logs bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache

USER www-data

EXPOSE 9000

# The answer of the process tells a running server from a dead one.
HEALTHCHECK --interval=30s --timeout=5s --retries=3 --start-period=20s \
  CMD php -r "exit(@fsockopen('127.0.0.1', 9000) ? 0 : 1);"

CMD ["php-fpm"]
