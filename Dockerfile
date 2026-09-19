# syntax=docker/dockerfile:1

# ---------------------------------------------------------------------------
# Assets stage: compiles the Vite/Tailwind bundle.
# ---------------------------------------------------------------------------
FROM node:20-alpine AS assets

WORKDIR /app

COPY package.json package-lock.json ./
RUN npm ci

COPY vite.config.js ./
COPY resources ./resources
RUN npm run build

# ---------------------------------------------------------------------------
# Application stage: PHP runtime shared by the web and queue containers.
#
# Composer lock requires PHP >= 8.4.1 (Symfony 8.x), so the image tracks the
# version the application is actually developed and tested against.
# ---------------------------------------------------------------------------
FROM php:8.4-cli AS app

# System libraries required to build the PHP extensions below, plus the
# PostgreSQL client used by the entrypoint to wait for the database.
RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        libpq-dev \
        libzip-dev \
        libonig-dev \
        unzip \
        postgresql-client \
    && docker-php-ext-install -j"$(nproc)" \
        pdo_pgsql \
        pgsql \
        mbstring \
        zip \
        pcntl \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Install PHP dependencies first so this layer is reused while application
# code changes. Scripts are skipped here because the application is not
# copied yet; package discovery runs after the full source is in place.
COPY composer.json composer.lock ./
RUN composer install \
        --no-dev \
        --no-interaction \
        --no-progress \
        --prefer-dist \
        --optimize-autoloader \
        --no-scripts

COPY . .

COPY --from=assets /app/public/build ./public/build

RUN mkdir -p \
        storage/framework/cache/data \
        storage/framework/sessions \
        storage/framework/views \
        storage/app/sarif-uploads \
        storage/logs \
        bootstrap/cache \
    && php artisan package:discover --ansi

COPY docker/entrypoint.sh /usr/local/bin/entrypoint
RUN chmod +x /usr/local/bin/entrypoint

EXPOSE 8000

ENTRYPOINT ["entrypoint"]

CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000", "--no-reload"]
