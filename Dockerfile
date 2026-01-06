# ===============================
# Stage 1: Assets (optional)
# ===============================
FROM node:16-alpine AS assets
WORKDIR /app

ARG SKIP_ASSETS=0
ENV NODE_OPTIONS=--openssl-legacy-provider

COPY package.json package-lock.json* yarn.lock* pnpm-lock.yaml* ./

RUN if [ "$SKIP_ASSETS" = "1" ]; then echo "SKIP_ASSETS=1 => skipping npm install"; \
    else \
      if [ -f package-lock.json ]; then npm ci; \
      elif [ -f yarn.lock ]; then yarn install --frozen-lockfile; \
      elif [ -f pnpm-lock.yaml ]; then corepack enable && pnpm i --frozen-lockfile; \
      else npm i; fi; \
    fi

# Copy only build-related files
COPY vite.config.* postcss.config.* tailwind.config.* ./
COPY webpack.mix.js* webpack.config.js* babel.config.js* .babelrc* tsconfig.json* ./

COPY resources/ resources/
COPY public/ public/

# If Mix expects resources/sass/app.scss and it doesn't exist, create a placeholder to avoid build crash
RUN if [ "$SKIP_ASSETS" = "1" ]; then echo "Skipping assets build"; \
    else \
      if [ -f webpack.mix.js ] && grep -q "resources/sass/app.scss" webpack.mix.js && [ ! -f resources/sass/app.scss ]; then \
        mkdir -p resources/sass && echo "/* placeholder */" > resources/sass/app.scss; \
      fi; \
      if npm run | grep -qE " production"; then npm run production; \
      elif npm run | grep -qE " prod"; then npm run prod; \
      elif npm run | grep -qE " build"; then npm run build; \
      else echo "No frontend build script found. Skipping assets build."; fi; \
    fi


# ===============================
# Stage 2: PHP + Nginx (Laravel)
# ===============================
FROM webdevops/php-nginx:8.2-alpine

ENV WEB_DOCUMENT_ROOT=/var/www/html/public \
    APP_ENV=production \
    APP_DEBUG=0

# Packages + php extensions + mysql client for import
RUN apk add --no-cache \
      git curl zip unzip bash icu-dev libzip-dev oniguruma-dev mariadb-client \
    && docker-php-ext-install pdo pdo_mysql \
    && rm -rf /var/cache/apk/*

WORKDIR /var/www/html
COPY . /var/www/html

# Copy built assets if any (doesn't fail if SKIP_ASSETS=1 and build produced nothing)
COPY --from=assets /app/public /var/www/html/public

# Composer
RUN set -eux; \
    curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer; \
    composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction --no-progress; \
    if [ ! -f .env ]; then cp .env.example .env || true; fi; \
    mkdir -p storage/framework/{cache,data,sessions,views} bootstrap/cache; \
    chown -R application:application storage bootstrap/cache; \
    chmod -R 775 storage bootstrap/cache

# Entrypoint scripts
RUN mkdir -p /opt/docker/provision/entrypoint.d
COPY docker/10-laravel-boot.sh /opt/docker/provision/entrypoint.d/10-laravel-boot.sh
COPY docker/20-db-init-and-migrate.sh /opt/docker/provision/entrypoint.d/20-db-init-and-migrate.sh
RUN chmod +x /opt/docker/provision/entrypoint.d/*.sh

EXPOSE 80
