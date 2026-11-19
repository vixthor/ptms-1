#!/usr/bin/env bash
#
# One-time provisioning for a fresh EC2 instance (Amazon Linux 2023 or Ubuntu 24.04).
# Installs Nginx, PHP 8.3-FPM, MySQL/MariaDB, Composer and Node 22, then
# clones, configures, builds, migrates and seeds the app.
#
# Usage: paste this whole file into EC2 "Advanced details > User data" at launch
# (progress: sudo tail -f /var/log/cloud-init-output.log), or on a running instance:
#   curl -fsSL https://raw.githubusercontent.com/vixthor/ptms-1/main/provision-ec2.sh -o provision-ec2.sh
#   sudo bash provision-ec2.sh
#
# Safe to re-run: existing swap, DB credentials, checkout and .env are kept.
# Optional overrides: REPO_URL, BRANCH, APP_DIR, DB_NAME, DB_USER, APP_URL
set -euo pipefail

# cloud-init runs user data as root without HOME set
export HOME="${HOME:-/root}"

REPO_URL="${REPO_URL:-https://github.com/vixthor/ptms-1.git}"
BRANCH="${BRANCH:-main}"
APP_DIR="${APP_DIR:-/var/www/ptms}"
DB_NAME="${DB_NAME:-ptms}"
DB_USER="${DB_USER:-ptms}"
PHP_VERSION="8.3"

if [[ $EUID -ne 0 ]]; then
    echo "Run with sudo: sudo bash $0" >&2
    exit 1
fi

# shellcheck disable=SC1091
source /etc/os-release
case "$ID" in
    amzn)
        OS_FAMILY=amazon
        APP_USER=ec2-user
        WEB_GROUP=nginx
        FPM_SERVICE=php-fpm
        FPM_SOCK=/run/php-fpm/www.sock
        DB_SERVICE=mariadb
        PHP_INIS=(/etc/php.ini)
        NGINX_SITE=/etc/nginx/conf.d/ptms.conf
        ;;
    ubuntu | debian)
        OS_FAMILY=debian
        APP_USER=ubuntu
        WEB_GROUP=www-data
        FPM_SERVICE=php${PHP_VERSION}-fpm
        FPM_SOCK=/run/php/php${PHP_VERSION}-fpm.sock
        DB_SERVICE=mysql
        PHP_INIS=(/etc/php/${PHP_VERSION}/fpm/php.ini /etc/php/${PHP_VERSION}/cli/php.ini)
        NGINX_SITE=/etc/nginx/sites-available/ptms
        ;;
    *)
        echo "Unsupported OS: $ID (use Amazon Linux 2023 or Ubuntu 24.04)" >&2
        exit 1
        ;;
esac
id "$APP_USER" >/dev/null 2>&1 || APP_USER="$(logname 2>/dev/null || echo root)"
as_app() { sudo -H -u "$APP_USER" "$@"; }

IMDS_TOKEN="$(curl -fsS -X PUT http://169.254.169.254/latest/api/token -H 'X-aws-ec2-metadata-token-ttl-seconds: 60' || true)"
PUBLIC_IP="$(curl -fsS -H "X-aws-ec2-metadata-token: ${IMDS_TOKEN}" http://169.254.169.254/latest/meta-data/public-ipv4 || hostname -I | awk '{print $1}')"
APP_URL="${APP_URL:-http://${PUBLIC_IP}}"

echo "==> Adding 2G swap (keeps composer/npm from running out of memory on small instances)"
if ! swapon --show | grep -q /swapfile; then
    [[ -f /swapfile ]] || dd if=/dev/zero of=/swapfile bs=1M count=2048 status=none
    chmod 600 /swapfile
    mkswap /swapfile
    swapon /swapfile
    grep -q '^/swapfile' /etc/fstab || echo '/swapfile none swap sw 0 0' >> /etc/fstab
fi

echo "==> Installing system packages"
if [[ $OS_FAMILY == amazon ]]; then
    dnf install -y nginx mariadb1011-server git unzip cronie nodejs22 nodejs22-npm \
        php${PHP_VERSION} php${PHP_VERSION}-fpm php${PHP_VERSION}-cli php${PHP_VERSION}-mysqlnd \
        php${PHP_VERSION}-pdo php${PHP_VERSION}-mbstring php${PHP_VERSION}-xml \
        php${PHP_VERSION}-zip php${PHP_VERSION}-gd php${PHP_VERSION}-bcmath \
        php${PHP_VERSION}-intl php${PHP_VERSION}-opcache php${PHP_VERSION}-sodium
    # nodejs22 installs versioned binaries; expose them as node/npm/npx
    for bin in node npm npx; do
        [[ -x /usr/bin/${bin}-22 ]] && ln -sf /usr/bin/${bin}-22 /usr/local/bin/${bin}
    done
    # Run PHP-FPM as the nginx user/group
    sed -i -e 's/^user = .*/user = nginx/' -e 's/^group = .*/group = nginx/' \
        -e 's/^;\?listen.owner = .*/listen.owner = nginx/' \
        -e 's/^;\?listen.group = .*/listen.group = nginx/' /etc/php-fpm.d/www.conf
    systemctl enable --now crond
else
    export DEBIAN_FRONTEND=noninteractive
    apt-get update -y
    apt-get install -y nginx mysql-server git unzip curl ufw \
        php${PHP_VERSION}-fpm php${PHP_VERSION}-cli php${PHP_VERSION}-mysql \
        php${PHP_VERSION}-mbstring php${PHP_VERSION}-xml php${PHP_VERSION}-curl \
        php${PHP_VERSION}-zip php${PHP_VERSION}-gd php${PHP_VERSION}-bcmath \
        php${PHP_VERSION}-intl php${PHP_VERSION}-sqlite3
    if ! command -v node >/dev/null || [[ "$(node -v | cut -d. -f1 | tr -d v)" -lt 22 ]]; then
        echo "==> Installing Node.js 22 (Vite 8 requires >= 20.19)"
        curl -fsSL https://deb.nodesource.com/setup_22.x | bash -
        apt-get install -y nodejs
    fi
fi
export PATH="/usr/local/bin:$PATH"

if ! command -v composer >/dev/null; then
    echo "==> Installing Composer"
    curl -fsSL https://getcomposer.org/installer -o /tmp/composer-setup.php
    php /tmp/composer-setup.php --install-dir=/usr/local/bin --filename=composer
    rm /tmp/composer-setup.php
fi

echo "==> Configuring PHP upload limits"
for ini in "${PHP_INIS[@]}"; do
    sed -i 's/^upload_max_filesize.*/upload_max_filesize = 20M/' "$ini"
    sed -i 's/^post_max_size.*/post_max_size = 25M/' "$ini"
    sed -i 's/^memory_limit.*/memory_limit = 256M/' "$ini"
done

echo "==> Creating database and user"
systemctl enable --now "$DB_SERVICE"
CRED_FILE=/root/ptms-db-credentials
if [[ -f "$CRED_FILE" ]]; then
    # shellcheck disable=SC1090
    source "$CRED_FILE"
else
    DB_PASS="$(openssl rand -base64 24 | tr -dc 'A-Za-z0-9' | head -c 24)"
    echo "DB_PASS=${DB_PASS}" > "$CRED_FILE"
    chmod 600 "$CRED_FILE"
fi
mysql -u root <<SQL
CREATE DATABASE IF NOT EXISTS \`${DB_NAME}\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS '${DB_USER}'@'localhost' IDENTIFIED BY '${DB_PASS}';
ALTER USER '${DB_USER}'@'localhost' IDENTIFIED BY '${DB_PASS}';
GRANT ALL PRIVILEGES ON \`${DB_NAME}\`.* TO '${DB_USER}'@'localhost';
FLUSH PRIVILEGES;
SQL

echo "==> Fetching application code"
FIRST_INSTALL=0
if [[ ! -d "$APP_DIR/.git" ]]; then
    FIRST_INSTALL=1
    mkdir -p "$(dirname "$APP_DIR")"
    git clone --branch "$BRANCH" "$REPO_URL" "$APP_DIR"
fi
git config --system --add safe.directory "$APP_DIR"

cd "$APP_DIR"

if [[ ! -f .env ]]; then
    echo "==> Writing production .env"
    cp .env.example .env
    set_env() {
        local key="$1" value="$2"
        if grep -qE "^#?\s*${key}=" .env; then
            sed -i -E "s|^#?\s*${key}=.*|${key}=${value}|" .env
        else
            echo "${key}=${value}" >> .env
        fi
    }
    set_env APP_NAME '"PTMS"'
    set_env APP_ENV production
    set_env APP_DEBUG false
    set_env APP_URL "$APP_URL"
    set_env LOG_LEVEL warning
    set_env DB_CONNECTION mysql
    set_env DB_HOST 127.0.0.1
    set_env DB_PORT 3306
    set_env DB_DATABASE "$DB_NAME"
    set_env DB_USERNAME "$DB_USER"
    set_env DB_PASSWORD "$DB_PASS"
    set_env SESSION_DRIVER database
    set_env CACHE_STORE database
    set_env QUEUE_CONNECTION database
    set_env FILESYSTEM_DISK local
fi

echo "==> Installing dependencies and building assets"
usermod -aG "$WEB_GROUP" "$APP_USER"
chown -R "$APP_USER:$WEB_GROUP" "$APP_DIR"
chmod o+x "$(dirname "$APP_DIR")"
as_app composer install --no-dev --optimize-autoloader --no-interaction
as_app npm ci
as_app npm run build

grep -q '^APP_KEY=base64' .env || as_app php artisan key:generate --force

echo "==> Setting permissions"
mkdir -p public/upload/{cbt,signature,staff,student,syllabus,teacher,website}
chown -R "$APP_USER:$WEB_GROUP" storage bootstrap/cache public/upload
chmod -R ug+rwX storage bootstrap/cache public/upload
find storage bootstrap/cache public/upload -type d -exec chmod g+s {} +
chmod 640 .env
chown "$APP_USER:$WEB_GROUP" .env
rm -f public/storage
as_app php artisan storage:link

echo "==> Running migrations"
as_app php artisan migrate --force
if [[ $FIRST_INSTALL -eq 1 ]]; then
    echo "==> Seeding initial data (admin user, permissions, website content)"
    as_app php artisan db:seed --force
    as_app php artisan db:seed --class=PermissionSeeder --force
    as_app php artisan db:seed --class=WebsiteContentSeeder --force
fi

as_app php artisan optimize

echo "==> Configuring Nginx"
cat > "$NGINX_SITE" <<NGINX
server {
    listen 80 default_server;
    listen [::]:80 default_server;
    server_name _;
    root ${APP_DIR}/public;
    index index.php;

    client_max_body_size 25M;
    charset utf-8;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    # Never execute PHP from user-uploaded directories
    location ~ ^/(upload|storage)/.*\.php$ { deny all; }

    error_page 404 /index.php;

    location ~ ^/index\.php(/|$) {
        fastcgi_pass unix:${FPM_SOCK};
        fastcgi_param SCRIPT_FILENAME \$realpath_root\$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    location ~ /\.(?!well-known).* { deny all; }
}
NGINX
if [[ $OS_FAMILY == amazon ]]; then
    # Drop the stock default server block so ours is the only default_server
    sed -i '/^    server {/,/^    }/d' /etc/nginx/nginx.conf
else
    ln -sf "$NGINX_SITE" /etc/nginx/sites-enabled/ptms
    rm -f /etc/nginx/sites-enabled/default
fi
systemctl enable "$FPM_SERVICE" nginx
systemctl restart "$FPM_SERVICE"
nginx -t
systemctl restart nginx

echo "==> Queue worker and scheduler"
cat > /etc/systemd/system/ptms-queue.service <<UNIT
[Unit]
Description=PTMS queue worker
After=network.target ${DB_SERVICE}.service

[Service]
User=${APP_USER}
Group=${WEB_GROUP}
Restart=always
WorkingDirectory=${APP_DIR}
ExecStart=/usr/bin/php ${APP_DIR}/artisan queue:work --sleep=3 --tries=3 --max-time=3600

[Install]
WantedBy=multi-user.target
UNIT
systemctl daemon-reload
systemctl enable --now ptms-queue

echo "* * * * * ${APP_USER} cd ${APP_DIR} && php artisan schedule:run >> /dev/null 2>&1" > /etc/cron.d/ptms-scheduler

if [[ $OS_FAMILY == debian ]]; then
    echo "==> Firewall"
    ufw allow OpenSSH
    ufw allow 'Nginx Full'
    ufw --force enable
fi

echo
echo "Done. Site: ${APP_URL}"
echo "DB password saved in ${CRED_FILE} (root only)."
if [[ $FIRST_INSTALL -eq 1 ]]; then
    echo "Seeded admin login: admin@gmail.com / admin  -- CHANGE THIS PASSWORD NOW."
fi
echo "Edit ${APP_DIR}/.env for MAIL_* and PAYSTACK_* keys, then run: php artisan optimize"
