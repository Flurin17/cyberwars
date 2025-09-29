#!/usr/bin/env bash
set -euo pipefail

# This script deploys the current project to an Ubuntu server running Nginx + PHP-FPM
# It should be executed as root. The site will run under www-data and listen on port 80.

APP_NAME="luzerner-moments"
APP_SRC_DIR="$(cd "$(dirname "$0")/.." && pwd)"
WEB_ROOT="/var/www/${APP_NAME}"
NGINX_SITE="/etc/nginx/sites-available/${APP_NAME}.conf"
NGINX_SITE_LINK="/etc/nginx/sites-enabled/${APP_NAME}.conf"
PHP_INI_SCAN_DIR="/etc/php"

echo "[1/7] Updating apt and installing dependencies..."
export DEBIAN_FRONTEND=noninteractive
apt-get update -y
apt-get install -y nginx php-fpm php-gd php-xml php-mbstring php-json php-cli unzip curl

echo "[2/7] Creating web root and syncing files..."
mkdir -p "$WEB_ROOT"
rsync -a --delete \
  --exclude deploy/ \
  --exclude .git/ \
  --exclude .gitignore \
  --exclude README.md \
  "$APP_SRC_DIR"/ "$WEB_ROOT"/

echo "[3/7] Setting permissions for www-data..."
chown -R www-data:www-data "$WEB_ROOT"
find "$WEB_ROOT" -type d -exec chmod 755 {} +
find "$WEB_ROOT" -type f -exec chmod 644 {} +
mkdir -p "$WEB_ROOT/uploads" "$WEB_ROOT/data"
chown -R www-data:www-data "$WEB_ROOT/uploads" "$WEB_ROOT/data"
chmod 775 "$WEB_ROOT/uploads" "$WEB_ROOT/data"

echo "[4/7] Configuring PHP-FPM upload limits..."
PHP_VERSION=$(php -r 'echo PHP_MAJOR_VERSION.".".PHP_MINOR_VERSION;')
PHP_FPM_INI="/etc/php/${PHP_VERSION}/fpm/php.ini"
if [ -f "$PHP_FPM_INI" ]; then
  sed -i 's/^;\?file_uploads\s*=.*/file_uploads = On/' "$PHP_FPM_INI"
  sed -i 's/^;\?upload_max_filesize\s*=.*/upload_max_filesize = 20M/' "$PHP_FPM_INI"
  sed -i 's/^;\?post_max_size\s*=.*/post_max_size = 25M/' "$PHP_FPM_INI"
  sed -i 's/^;\?memory_limit\s*=.*/memory_limit = 256M/' "$PHP_FPM_INI"
  systemctl restart php${PHP_VERSION}-fpm || true
fi

echo "[5/7] Writing Nginx server block..."
cat > "$NGINX_SITE" <<NGINX
server {
    listen 80;
    server_name _;

    root ${WEB_ROOT};
    index index.php index.html;

    access_log /var/log/nginx/${APP_NAME}_access.log;
    error_log  /var/log/nginx/${APP_NAME}_error.log;

    location /assets/ {
        try_files $uri =404;
    }

    location /uploads/ {
        alias ${WEB_ROOT}/uploads/;
        autoindex off;
        add_header Content-Security-Policy "default-src 'self'; img-src 'self' data:;" always;
        try_files $uri =404;
    }

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php${PHP_VERSION}-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~* \.(php|phar|phtml)$ {
        return 403;
    }
}
NGINX

ln -sf "$NGINX_SITE" "$NGINX_SITE_LINK"

echo "[6/7] Enabling services and opening firewall..."
systemctl enable nginx
systemctl restart nginx

if command -v ufw >/dev/null 2>&1; then
  ufw allow 80/tcp || true
fi

echo "[7/7] Cleaning default site and finalizing..."
if [ -f /etc/nginx/sites-enabled/default ]; then
  rm -f /etc/nginx/sites-enabled/default
fi
nginx -t
systemctl reload nginx

echo "Deployment complete. Site should be available on port 80."


