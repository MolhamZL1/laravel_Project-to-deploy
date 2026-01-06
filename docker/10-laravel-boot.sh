#!/bin/sh
set -e

echo "=== Laravel boot ==="
cd /var/www/html

mkdir -p storage/framework/{cache,data,sessions,views} bootstrap/cache storage/logs
chown -R application:application storage bootstrap/cache || true
chmod -R 775 storage bootstrap/cache || true

php artisan storage:link || true

echo "=== Boot done ==="
