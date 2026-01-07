# ==============================
# Stage 1: Frontend build (Node)
# ==============================
FROM node:18-alpine AS assets

WORKDIR /app
ENV NODE_OPTIONS=--max-old-space-size=1024

COPY package*.json ./
RUN npm ci --no-audit --no-fund --omit=optional

COPY . .
RUN npm run build

# ==============================
# Stage 2: PHP base
# ==============================
FROM php:8.1-fpm-alpine AS base

RUN apk add --no-cache \
    git curl \
    libpng-dev libjpeg-turbo-dev freetype-dev \
    oniguruma-dev icu-dev libzip-dev mariadb-client \
    && apk add --no-cache --virtual .build-deps $PHPIZE_DEPS \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd zip intl mysqli pdo_mysql opcache mbstring bcmath \
    && apk del .build-deps

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# ==============================
# Stage 3: Laravel build
# ==============================
FROM base AS builder
WORKDIR /var/www/html

COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader

COPY . .
COPY --from=assets /app/public /var/www/html/public

RUN php artisan view:clear && php artisan config:clear

# ==============================
# Stage 4: Production
# ==============================
FROM php:8.1-fpm-alpine

RUN apk add --no-cache nginx supervisor curl

WORKDIR /var/www/html
COPY --from=builder /var/www/html /var/www/html

RUN mkdir -p storage/framework/{sessions,views,cache} storage/logs bootstrap/cache \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 775 storage bootstrap/cache

EXPOSE 80
CMD ["/usr/bin/supervisord","-c","/etc/supervisor/conf.d/supervisord.conf"]
