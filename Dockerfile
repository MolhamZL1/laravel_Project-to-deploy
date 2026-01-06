# ====== Stage 1: Build frontend assets (Laravel Mix) ======
FROM node:16-alpine AS assets
WORKDIR /app

COPY package.json package-lock.json* yarn.lock* pnpm-lock.yaml* ./

RUN if [ -f package-lock.json ]; then npm ci; \
    elif [ -f yarn.lock ]; then yarn install --frozen-lockfile; \
    elif [ -f pnpm-lock.yaml ]; then corepack enable && pnpm i --frozen-lockfile; \
    else npm i; fi

# Laravel Mix config files
COPY webpack.mix.js* webpack.config.js* babel.config.js* .babelrc* tsconfig.json* ./

# Frontend sources
COPY resources/ resources/
COPY public/ public/

# Build (Mix)
RUN if npm run | grep -qE " production"; then npm run production; \
    elif npm run | grep -qE " prod"; then npm run prod; \
    elif npm run | grep -qE " build"; then npm run build; \
    else echo "No frontend build script found. Skipping assets build."; fi


# ====== Stage 2: PHP + Nginx (Laravel app) ======
FROM webdevops/php-nginx:8.2-alpine

ENV WEB_DOCUMENT_ROOT=/var/www/html/public \
    APP_ENV=production \
    APP_DEBUG=0

WORKDIR /var/www/html

# Tools
RUN apk add --no-cache git curl zip unzip bash \
    icu-dev libzip-dev oniguruma-dev \
    && rm -rf /var/cache/apk/*

# Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# ✅ Copy full project first (حتى ما تفشل scripts)
COPY . .

# ✅ Composer install (بدون dev)
RUN composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction --no-progress

# ✅ Copy built assets from stage 1 (Mix outputs)
COPY --from=assets /app/public/mix-manifest.json /var/www/html/public/mix-manifest.json
COPY --from=assets /app/public/js /var/www/html/public/js
COPY --from=assets /app/public/css /var/www/html/public/css

# Permissions
RUN chown -R application:application storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# Boot scripts
RUN mkdir -p /opt/docker/provision/entrypoint.d
COPY docker/10-laravel-boot.sh /opt/docker/provision/entrypoint.d/10-laravel-boot.sh
RUN chmod +x /opt/docker/provision/entrypoint.d/*.sh

EXPOSE 80
