# StudyLib en production : nginx + php-fpm (serversideup/php, utilisateur non root),
# servi par la passerelle de la plateforme sous /biblio, jamais exposé directement.

# ── Dépendances PHP (sans outils de développement) ──────────────────────────────
FROM composer:2 AS vendor
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist --no-interaction --ignore-platform-req=ext-*
COPY . .
RUN composer dump-autoload --optimize --no-dev --classmap-authoritative

# ── Ressources front (Vite) ─────────────────────────────────────────────────────
FROM node:22-alpine AS assets
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci --no-audit --no-fund
COPY --from=vendor /app /app
RUN npm run build

# ── Exécution ──────────────────────────────────────────────────────────────────
FROM serversideup/php:8.3-fpm-nginx
ENV PHP_OPCACHE_ENABLE=1 \
    SSL_MODE=off
WORKDIR /var/www/html
COPY --chown=www-data:www-data --from=vendor /app /var/www/html
COPY --chown=www-data:www-data --from=assets /app/public/build /var/www/html/public/build
USER www-data
