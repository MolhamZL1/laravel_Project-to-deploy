#!/bin/sh
set -e

echo "=== DB init + migrate on boot ==="

# لازم ENV يكون مضبوط بالمنصة
: "${DB_HOST:?DB_HOST is required}"
: "${DB_PORT:?DB_PORT is required}"
: "${DB_DATABASE:?DB_DATABASE is required}"
: "${DB_USERNAME:?DB_USERNAME is required}"
: "${DB_PASSWORD:?DB_PASSWORD is required}"

export MYSQL_PWD="$DB_PASSWORD"

echo "Waiting for database connection..."
for i in $(seq 1 60); do
  if mysql -h "$DB_HOST" -P "$DB_PORT" -u "$DB_USERNAME" -e "SELECT 1" >/dev/null 2>&1; then
    echo "DB OK"
    break
  fi
  echo "DB not ready... ($i/60)"
  sleep 2
done

# تأكد أن DB موجودة
mysql -h "$DB_HOST" -P "$DB_PORT" -u "$DB_USERNAME" -e "USE \`$DB_DATABASE\`;" >/dev/null 2>&1 || {
  echo "ERROR: Cannot use database $DB_DATABASE"
  exit 1
}

# إذا flash_deals مو موجود → غالبًا DB فاضية → استورد SQL
HAS_FLASH_DEALS=$(mysql -h "$DB_HOST" -P "$DB_PORT" -u "$DB_USERNAME" -D "$DB_DATABASE" -Nse "SHOW TABLES LIKE 'flash_deals';" || true)

if [ -z "$HAS_FLASH_DEALS" ]; then
  echo "flash_deals not found. Assuming fresh DB. Trying SQL import..."

  SQL_FILE=$(find /var/www/html -maxdepth 6 -type f -name "*.sql" | head -n 1 || true)

  if [ -n "$SQL_FILE" ]; then
    echo "Importing SQL: $SQL_FILE"
    mysql -h "$DB_HOST" -P "$DB_PORT" -u "$DB_USERNAME" "$DB_DATABASE" < "$SQL_FILE"
    echo "SQL import done."
  else
    echo "WARNING: No SQL file found in project. Will continue migrations only."
  fi
else
  echo "flash_deals exists. Skipping SQL import."
fi

# Fix permissions (لأن cache:clear كان يفشل عندك)
chown -R application:application /var/www/html/storage /var/www/html/bootstrap/cache || true
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache || true

php artisan config:clear || true
php artisan cache:clear || true

# migrate (هذا اللي ينشئ guest_users وباقي الجداول)
php artisan migrate --force

echo "=== DB init + migrate done ==="
