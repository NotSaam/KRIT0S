# ── 1. PHP dependencies (also needed by the Vite build: Ziggy lives in vendor/) ──
FROM composer:2 AS vendor
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --no-interaction --prefer-dist --ignore-platform-reqs

# ── 2. Frontend assets ────────────────────────────────────────────────
FROM node:22-alpine AS assets
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci --no-audit --no-fund
COPY . .
COPY --from=vendor /app/vendor ./vendor
RUN npm run build

# ── 3. Runtime ────────────────────────────────────────────────────────
FROM php:8.4-apache

RUN apt-get update && apt-get install -y --no-install-recommends libpq-dev libzip-dev libicu-dev libpng-dev unzip \
    && docker-php-ext-install pdo_pgsql intl zip gd opcache \
    && a2enmod rewrite headers \
    && rm -rf /var/lib/apt/lists/*

ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf /etc/apache2/apache2.conf \
    && printf 'ServerTokens Prod\nServerSignature Off\n' > /etc/apache2/conf-enabled/hardening.conf

WORKDIR /var/www/html
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
COPY . .
COPY --from=vendor /app/vendor ./vendor
COPY --from=assets /app/public/build ./public/build
RUN composer dump-autoload --no-dev --optimize \
    && php artisan package:discover \
    && chown -R www-data:www-data storage bootstrap/cache

COPY docker/entrypoint.sh /usr/local/bin/krit0s-entrypoint
RUN chmod +x /usr/local/bin/krit0s-entrypoint

ENTRYPOINT ["krit0s-entrypoint"]
CMD ["apache2-foreground"]
