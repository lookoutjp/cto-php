#!/usr/bin/env bash
# Laravel Forge の Deploy Script 用。Laravel Cloud は不要（ビルド設定で行う）。
set -euo pipefail

cd "${FORGE_SITE_PATH:-$(dirname "$0")}"

git pull origin main

composer install --no-dev --optimize-autoloader --no-interaction

npm ci
npm run build

php artisan migrate --force

php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

if [ -f artisan ]; then
    ( flock -w 10 9 || exit 1
      echo 'Restarting FPM...'
      sudo -S service "php${PHP_VERSION:-8.3}-fpm" reload ) 9>/tmp/fpmlock || true
fi

php artisan queue:restart || true
