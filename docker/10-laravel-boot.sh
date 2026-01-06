#!/usr/bin/env bash
set -e

echo "=== Laravel boot ==="

cd /var/www/html

# Ensure writable dirs exist + permissions (before artisan)
mkdir -p storage/framework/{cache,data,sessions,views} bootstrap/cache storage/logs
chown -R application:application storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

# storage:link (non-fatal)
php artisan storage:link >/dev/null 2>&1 || true

# Cache (only if artisan exists)
php artisan config:cache --no-interaction || true
php artisan route:cache --no-interaction || true
php artisan view:cache --no-interaction || true

echo "=== Boot done ==="
