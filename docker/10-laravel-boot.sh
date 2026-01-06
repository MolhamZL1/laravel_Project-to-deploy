#!/usr/bin/env bash
set -e
cd /var/www/html

# إذا APP_KEY فاضي بالمتحوّلات، ولّده
if [ -z "${APP_KEY}" ] || [ "${APP_KEY}" = "null" ]; then
  php artisan key:generate --force || true
fi

# كاشات الإنتاج
php artisan config:cache || true
php artisan route:cache || true
php artisan view:cache || true

# صلاحيات
chown -R application:application storage bootstrap/cache || true
chmod -R 775 storage bootstrap/cache || true
