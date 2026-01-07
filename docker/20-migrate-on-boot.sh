#!/bin/sh
set -e

echo "=== Laravel migrate on boot ==="

# انتظر DB شوي (اختياري لكنه مهم)
echo "Waiting for database connection..."
for i in $(seq 1 30); do
  php -r "try { new PDO('mysql:host=' . getenv('DB_HOST') . ';port=' . getenv('DB_PORT') . ';dbname=' . getenv('DB_DATABASE'), getenv('DB_USERNAME'), getenv('DB_PASSWORD')); echo 'DB OK\n'; } catch (Exception \$e) { echo 'DB not ready\n'; exit(1); }" \
    && break || true
  sleep 2
done

# امسح كاش الكونفيغ (مهم إذا تغيرت env)
php artisan config:clear || true
php artisan cache:clear || true

# migrate (هذا اللي بيعمل جدول guest_users)
php artisan migrate --force

# (اختياري) إذا مشروعك يحتاج seed:
# php artisan db:seed --force

echo "=== Migrate done ==="
