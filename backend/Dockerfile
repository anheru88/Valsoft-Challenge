# syntax=docker/dockerfile:1

# --- Stage 1: install PHP dependencies with Composer -------------------------
FROM composer:2 AS vendor
WORKDIR /app
COPY composer.json composer.lock ./
# Scripts run artisan, which needs the full app — defer them to runtime.
RUN composer install \
    --no-dev --no-scripts --no-interaction \
    --prefer-dist --no-progress --optimize-autoloader

# --- Stage 2: runtime --------------------------------------------------------
FROM php:8.4-cli-alpine

# mbstring + pdo_mysql are the two extensions Laravel/MariaDB need beyond the
# defaults; oniguruma is mbstring's build dependency, removed after compiling.
RUN apk add --no-cache oniguruma \
    && apk add --no-cache --virtual .build-deps $PHPIZE_DEPS oniguruma-dev \
    && docker-php-ext-install -j"$(nproc)" pdo_mysql mbstring \
    && apk del .build-deps

WORKDIR /var/www/html

COPY --from=vendor /app/vendor ./vendor
COPY . .

RUN chmod +x docker/entrypoint.sh \
    && mkdir -p storage/framework/cache storage/framework/sessions \
               storage/framework/views storage/logs bootstrap/cache \
    # Drop any cached package/config manifest from the build context: it may
    # list dev-only providers (e.g. Pail) that --no-dev did not install.
    # Laravel regenerates it on first boot from what is actually in vendor.
    && rm -f bootstrap/cache/*.php \
    && chown -R www-data:www-data storage bootstrap/cache

EXPOSE 8000
ENTRYPOINT ["docker/entrypoint.sh"]
