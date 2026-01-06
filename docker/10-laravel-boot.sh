#!/bin/sh
set -e

echo "=== Laravel boot ==="

php artisan storage:link || true

chown -R application:application /var/www/html/storage /var/www/html/bootstrap/cache || true
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache || true

echo "=== Boot done ==="
