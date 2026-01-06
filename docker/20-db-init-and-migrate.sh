#!/bin/sh
set -e

echo "=== DB init + migrate on boot ==="

: "${DB_HOST:?DB_HOST is required}"
: "${DB_PORT:?DB_PORT is required}"
: "${DB_DATABASE:?DB_DATABASE is required}"
: "${DB_USERNAME:?DB_USERNAME is required}"
: "${DB_PASSWORD:?DB_PASSWORD is required}"

export MYSQL_PWD="$DB_PASSWORD"

cd /var/www/html

echo "Waiting for database connection..."
for i in $(seq 1 60); do
  if mariadb -h "$DB_HOST" -P "$DB_PORT" -u "$DB_USERNAME" -e "SELECT 1" >/dev/null 2>&1; then
    echo "DB OK"
    break
  fi
  echo "DB not ready... ($i/60)"
  sleep 2
done

# تحقق DB
mariadb -h "$DB_HOST" -P "$DB_PORT" -u "$DB_USERNAME" -e "USE \`$DB_DATABASE\`;" >/dev/null 2>&1 || {
  echo "ERROR: Cannot use database $DB_DATABASE"
  exit 1
}

# إصلاح صلاحيات storage (لحل Failed to clear cache)
mkdir -p storage/framework/{cache,data,sessions,views} bootstrap/cache storage/logs
chown -R application:application storage bootstrap/cache || true
chmod -R 775 storage bootstrap/cache || true
rm -rf storage/framework/cache/* storage/framework/views/* storage/framework/sessions/* || true
chown -R application:application storage bootstrap/cache || true
chmod -R 775 storage bootstrap/cache || true

# هل flash_deals موجود؟
HAS_FLASH_DEALS=$(mariadb -h "$DB_HOST" -P "$DB_PORT" -u "$DB_USERNAME" -D "$DB_DATABASE" -Nse "SHOW TABLES LIKE 'flash_deals';" || true)

if [ -z "$HAS_FLASH_DEALS" ]; then
  echo "flash_deals not found. Trying to import the LARGEST SQL dump in project..."

  # اختر أكبر ملف sql داخل المشروع (بدون -printf للتوافق)
  SQL_FILE=$(
    find /var/www/html -maxdepth 8 -type f -name "*.sql" -exec sh -c '
      for f do
        s=$(stat -c%s "$f" 2>/dev/null || echo 0)
        echo "$s $f"
      done
    ' sh {} + 2>/dev/null | sort -nr | head -n 1 | cut -d" " -f2-
  )

  if [ -z "$SQL_FILE" ]; then
    echo "ERROR: No .sql files found in project."
    exit 1
  fi

  # حماية: لو الملف صغير جداً غالباً مو dump كامل
  SQL_SIZE=$(stat -c%s "$SQL_FILE" 2>/dev/null || echo 0)
  echo "Picked SQL: $SQL_FILE (size=$SQL_SIZE bytes)"

  if [ "$SQL_SIZE" -lt 200000 ]; then
    echo "ERROR: SQL file is too small to be a full dump. (likely partial like payment_requests.sql)"
    echo "Put the REAL full dump SQL inside the repo and redeploy."
    exit 1
  fi

  echo "Importing SQL dump..."
  mariadb -h "$DB_HOST" -P "$DB_PORT" -u "$DB_USERNAME" "$DB_DATABASE" < "$SQL_FILE"
  echo "SQL import done."
else
  echo "flash_deals exists. Skipping SQL import."
fi

# حاول clear cache (حتى لو فشل لا نوقف)
php artisan config:clear || true
php artisan cache:clear || true

echo "Running migrations..."
php artisan migrate --force --no-interaction

echo "=== Done ==="
