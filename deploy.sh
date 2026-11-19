#!/usr/bin/env bash
#
# Pull the latest code and rebuild on the EC2 instance.
# Usage (on the instance): bash /var/www/ptms/deploy.sh
set -euo pipefail

APP_DIR="${APP_DIR:-/var/www/ptms}"
BRANCH="${BRANCH:-main}"

cd "$APP_DIR"

php artisan down --retry=15 || true
trap 'php artisan up' EXIT

git fetch origin "$BRANCH"
git reset --hard "origin/$BRANCH"

composer install --no-dev --optimize-autoloader --no-interaction
npm ci
npm run build

php artisan migrate --force
php artisan optimize
php artisan queue:restart

if systemctl list-unit-files php-fpm.service >/dev/null 2>&1; then
    sudo systemctl reload php-fpm
else
    sudo systemctl reload php8.3-fpm
fi

echo "Deployed $(git rev-parse --short HEAD)"
