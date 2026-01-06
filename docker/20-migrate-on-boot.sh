#!/bin/sh
set -e

echo "=== Laravel migrate on boot ==="

# انتظار DB
echo "Waiting for database connection..."
for i in $(seq 1 30); do
  php -r "
  try {
    new PDO(
      'mysql:host=' . getenv('DB_HOST') . ';port=' . getenv('DB_PORT') . ';dbname=' . getenv('DB_DATABASE'),
      getenv('DB_USERNAME'),
      getenv('DB_PASSWORD')
    );
    echo 'DB OK\n';
    exit(0);
  } catch (Exception \$e) {
    exit(1);
  }" && break || true
  sleep 2
done

# تنظيف كاش (بدون ما يوقف على permissions)
php artisan config:clear || true
php artisan cache:clear || true

echo "Running migrations..."
if php artisan migrate --force; then
  echo "Migrations completed successfully."
else
  echo "⚠️ Migrations failed (likely due to missing tables)."
  echo "Trying to continue without stopping container..."
fi

echo "=== Migrate step finished ==="
exit 0
