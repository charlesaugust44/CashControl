#!/bin/sh
set -e

chown -R 1000:1000 /var/www/html/storage \
    /var/www/html/database \
    /var/www/html/bootstrap/cache 2>/dev/null || true

php artisan config:cache
php artisan route:cache
php artisan view:cache

php artisan migrate --force

exec "$@"
