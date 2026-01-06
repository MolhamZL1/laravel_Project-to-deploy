# ====== Stage 1: Build frontend assets (Mix / Vite) ======
FROM node:20-alpine AS assets
WORKDIR /app

COPY package.json package-lock.json* yarn.lock* pnpm-lock.yaml* ./

RUN if [ -f package-lock.json ]; then npm ci; \
    elif [ -f yarn.lock ]; then yarn install --frozen-lockfile; \
    elif [ -f pnpm-lock.yaml ]; then corepack enable && pnpm i --frozen-lockfile; \
    else npm i; fi

# ✅ مهم لمشاريع Laravel Mix
COPY webpack.mix.js* webpack.config.js* babel.config.js* .babelrc* tsconfig.json* ./

# ملفات الواجهة
COPY resources/ resources/
COPY public/ public/

# نفّذ build حسب الموجود
RUN if npm run | grep -qE " build"; then npm run build; \
    elif npm run | grep -qE " production"; then npm run production; \
    elif npm run | grep -qE " prod"; then npm run prod; \
    else echo "No frontend build script found. Skipping assets build."; fi

# ====== Stage 2: PHP + Nginx (Laravel app) ======
FROM webdevops/php-nginx:8.2-alpine

ENV WEB_DOCUMENT_ROOT=/var/www/html/public \
    APP_ENV=production \
    APP_DEBUG=0

WORKDIR /var/www/html

# ✅ أدوات أساسية + PHP extensions (ملاحظة: docker-php-ext-install قد لا يكون موجود دائماً)
RUN apk add --no-cache \
      git curl zip unzip bash \
      icu-dev libzip-dev oniguruma-dev \
    && rm -rf /var/cache/apk/*

# ✅ Composer (نزّله مرة)
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# ✅ انسخ composer files أولاً للاستفادة من الكاش
COPY composer.json composer.lock* ./
RUN composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction --no-progress || true

# ✅ انسخ باقي المشروع
COPY . .

# ✅ انسخ الأصول المبنية (إن وُجدت)
# Vite output
COPY --from=assets /app/public/build /var/www/html/public/build
# Mix output (بعض المشاريع تنتج public/mix-manifest.json و public/js/css)
COPY --from=assets /app/public/mix-manifest.json /var/www/html/public/mix-manifest.json
COPY --from=assets /app/public/js /var/www/html/public/js
COPY --from=assets /app/public/css /var/www/html/public/css

# ✅ صلاحيات Laravel
RUN chown -R application:application storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# ✅ لا تنسخ .env داخل image (خليه من environment variables / secrets)
# ❌ حذفنا: cp .env.example .env

# سكربتات الإقلاع
RUN mkdir -p /opt/docker/provision/entrypoint.d
COPY docker/10-laravel-boot.sh /opt/docker/provision/entrypoint.d/10-laravel-boot.sh
RUN chmod +x /opt/docker/provision/entrypoint.d/*.sh

EXPOSE 80
