#!/bin/sh
set -e

echo "=== Laravel cache on boot ==="

php artisan config:cache || true
php artisan route:cache || true
php artisan view:cache || true

echo "=== Cache done ==="
