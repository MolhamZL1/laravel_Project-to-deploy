FROM webdevops/php-nginx:8.2-alpine

ENV WEB_DOCUMENT_ROOT=/var/www/html/public \
    APP_ENV=production \
    APP_DEBUG=0

WORKDIR /var/www/html

RUN apk add --no-cache git curl zip unzip bash \
    icu-dev libzip-dev oniguruma-dev \
    && rm -rf /var/cache/apk/*

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

COPY . .

RUN composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction --no-progress \
    && php artisan storage:link || true \
    && chown -R application:application storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

RUN mkdir -p /opt/docker/provision/entrypoint.d
COPY docker/10-laravel-boot.sh /opt/docker/provision/entrypoint.d/10-laravel-boot.sh
RUN chmod +x /opt/docker/provision/entrypoint.d/*.sh

EXPOSE 80
