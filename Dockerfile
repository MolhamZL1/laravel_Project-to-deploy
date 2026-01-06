FROM webdevops/php-nginx:8.2-alpine

ENV WEB_DOCUMENT_ROOT=/var/www/html/public \
    APP_ENV=production \
    APP_DEBUG=0

WORKDIR /var/www/html

# Tools + mysql-client مهم للاستيراد
RUN apk add --no-cache \
      git curl zip unzip bash \
      icu-dev libzip-dev oniguruma-dev \
      mysql-client \
    && rm -rf /var/cache/apk/*

# Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Copy project
COPY . .

# Install dependencies
RUN composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction --no-progress

# Permissions (مهم قبل تشغيل السكربتات)
RUN chown -R application:application storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# Entrypoint scripts
RUN mkdir -p /opt/docker/provision/entrypoint.d

COPY docker/10-laravel-boot.sh /opt/docker/provision/entrypoint.d/10-laravel-boot.sh
COPY docker/20-db-init-and-migrate.sh /opt/docker/provision/entrypoint.d/20-db-init-and-migrate.sh
COPY docker/30-cache-on-boot.sh /opt/docker/provision/entrypoint.d/30-cache-on-boot.sh

RUN chmod +x /opt/docker/provision/entrypoint.d/*.sh

EXPOSE 80
