# ====== Stage 1: Build frontend assets with Vite ======
FROM node:20-alpine AS assets
WORKDIR /app

# انسخ ملفات الـ npm أولاً للاستفادة من الكاش
COPY package.json package-lock.json* yarn.lock* pnpm-lock.yaml* ./
# ثبّت الاعتمادات (اختَر ما يتوفر لديك من lock files)
RUN if [ -f package-lock.json ]; then npm ci; \
    elif [ -f yarn.lock ]; then yarn install --frozen-lockfile; \
    elif [ -f pnpm-lock.yaml ]; then corepack enable && pnpm i --frozen-lockfile; \
    else npm i; fi

# انسخ باقي الملفات اللازمة للبناء
COPY vite.config.* postcss.config.* tailwind.config.* ./
COPY resources/ resources/

# نفّذ البناء (laravel-vite-plugin يخرج لـ public/build افتراضياً)
RUN npm run build

# ====== Stage 2: PHP + Nginx (Laravel app) ======
FROM webdevops/php-nginx:8.2-alpine

ENV WEB_DOCUMENT_ROOT=/var/www/html/public \
    APP_ENV=production \
    APP_DEBUG=0

# امتدادات PHP أساسية
RUN apk add --no-cache git curl zip unzip bash icu-dev libzip-dev oniguruma-dev \
    && docker-php-ext-install pdo pdo_mysql \
    && rm -rf /var/cache/apk/*

WORKDIR /var/www/html
COPY . /var/www/html

# انسخ الأصول المبنية من المرحلة الأولى
COPY --from=assets /app/public/build /var/www/html/public/build

# Composer
RUN set -ex \
    && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
    && composer install --no-dev --prefer-dist --optimize-autoloader \
    && if [ ! -f .env ]; then cp .env.example .env || true; fi \
    && php artisan storage:link || true \
    && chown -R application:application storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# سكربتات الإقلاع (كاش + صلاحيات + اختياري: مهاجرات)
RUN mkdir -p /opt/docker/provision/entrypoint.d
COPY docker/10-laravel-boot.sh /opt/docker/provision/entrypoint.d/10-laravel-boot.sh
# (اختياري) لو أضفت سكربت المايغريشن:
# COPY docker/20-migrate-on-boot.sh /opt/docker/provision/entrypoint.d/20-migrate-on-boot.sh
RUN chmod +x /opt/docker/provision/entrypoint.d/*.sh

EXPOSE 80
