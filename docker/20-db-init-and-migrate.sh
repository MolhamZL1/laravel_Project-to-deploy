#!/usr/bin/env bash
set -e

echo "=== DB init + migrate on boot ==="

cd /var/www/html

DB_HOST="${DB_HOST:-mysql}"
DB_PORT="${DB_PORT:-3306}"
DB_DATABASE="${DB_DATABASE:-mydb}"
DB_USERNAME="${DB_USERNAME:-root}"
DB_PASSWORD="${DB_PASSWORD:-}"

# Where you will place the FULL schema dump (create tables)
DB_SCHEMA_DUMP="${DB_SCHEMA_DUMP:-/var/www/html/docker/db/schema.sql}"

# Wait for DB
echo "Waiting for database connection..."
for i in $(seq 1 60); do
  php -r "
    try {
      new PDO('mysql:host=$DB_HOST;port=$DB_PORT;dbname=$DB_DATABASE', '$DB_USERNAME', '$DB_PASSWORD');
      echo 'DB OK';
    } catch (Exception \$e) { exit(1); }
  " >/dev/null 2>&1 && break
  sleep 2
done

php -r "
  try {
    new PDO('mysql:host=$DB_HOST;port=$DB_PORT;dbname=$DB_DATABASE', '$DB_USERNAME', '$DB_PASSWORD');
    echo \"DB OK\n\";
  } catch (Exception \$e) {
    echo \"DB NOT READY\n\";
    exit(1);
  }
"

# Check if migrations table exists
HAS_MIGRATIONS_TABLE=$(php -r "
  try {
    \$pdo=new PDO('mysql:host=$DB_HOST;port=$DB_PORT;dbname=information_schema', '$DB_USERNAME', '$DB_PASSWORD');
    \$stmt=\$pdo->prepare('SELECT COUNT(*) FROM TABLES WHERE TABLE_SCHEMA=? AND TABLE_NAME=?');
    \$stmt->execute(['$DB_DATABASE','migrations']);
    echo (int)\$stmt->fetchColumn();
  } catch (Exception \$e) { echo 0; }
")

if [ "$HAS_MIGRATIONS_TABLE" -eq 0 ]; then
  echo "Fresh DB detected (no migrations table)."

  if [ -f "$DB_SCHEMA_DUMP" ]; then
    echo "Importing FULL schema dump: $DB_SCHEMA_DUMP"
    # Use mariadb client (mysql alias). MYSQL_PWD avoids showing password in process list
    export MYSQL_PWD="$DB_PASSWORD"
    mariadb -h"$DB_HOST" -P"$DB_PORT" -u"$DB_USERNAME" "$DB_DATABASE" < "$DB_SCHEMA_DUMP"
    echo "Schema import done."
  else
    echo "ERROR: No schema dump found at $DB_SCHEMA_DUMP"
    echo "You MUST add a full DB schema SQL dump there (create tables), otherwise migrations will fail on ALTERs."
    exit 1
  fi
fi

# Fix permissions again before artisan cache operations
mkdir -p storage/framework/{cache,data,sessions,views} bootstrap/cache storage/logs
chown -R application:application storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

# Now run migrations
echo "Running migrations..."
php artisan migrate --force --no-interaction

echo "=== DB init + migrate done ==="
