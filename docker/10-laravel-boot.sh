#!/bin/sh
set -e

php artisan storage:link || true
php artisan view:cache || true
php artisan route:cache || true
php artisan config:cache || true

chown -R application:application storage bootstrap/cache || true
chmod -R 775 storage bootstrap/cache || true
