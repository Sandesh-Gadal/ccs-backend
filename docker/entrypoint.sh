#!/bin/sh
set -e

cd /var/www/html

# Wait for MySQL to accept connections before touching the database
echo "Waiting for database at ${DB_HOST}:${DB_PORT:-3306}..."
for i in $(seq 1 30); do
  if php -r 'try { new PDO("mysql:host=".getenv("DB_HOST").";port=".(getenv("DB_PORT") ?: 3306), getenv("DB_USERNAME"), getenv("DB_PASSWORD")); exit(0); } catch (Throwable $e) { exit(1); }'; then
    echo "Database is ready."
    break
  fi
  sleep 2
done

# Uploaded product images live on a volume; make sure the folders exist
mkdir -p storage/app/public/products storage/framework/cache storage/framework/sessions storage/framework/views storage/logs
chown -R www-data:www-data storage bootstrap/cache

[ -L public/storage ] || php artisan storage:link

if [ "${RUN_MIGRATIONS:-true}" = "true" ]; then
  php artisan migrate --force
fi

php artisan optimize:clear >/dev/null
php artisan config:cache
php artisan route:cache
php artisan view:cache

exec "$@"
