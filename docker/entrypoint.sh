#!/bin/sh
set -e

chown -R www-data:www-data database
chmod -R 775 database

php artisan config:cache
php artisan route:cache
php artisan view:cache

php artisan migrate --force

exec "$@"
