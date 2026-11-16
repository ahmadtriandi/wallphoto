#!/usr/bin/env bash
# ══════════════════════════════════════════════════════════════════
#  Setup VPS pertama kali (Ubuntu 24.04). Jalankan sebagai root:
#      bash /var/www/gallery-app/deploy/setup-server.sh
#  Isi DOMAIN dan EMAIL di bawah dulu.
# ══════════════════════════════════════════════════════════════════
set -euo pipefail

DOMAIN="wall.domainkamu.com"      # domain yang sudah diarahkan ke IP VPS
EMAIL="email@domainkamu.com"      # untuk sertifikat SSL (Let's Encrypt)

PHP="8.3"
DB_NAME="gallery_db"
DB_USER="gallery"
APP_DIR="$(cd "$(dirname "$0")/.." && pwd)"

if [ "$DOMAIN" = "wall.domainkamu.com" ] || [ "$EMAIL" = "email@domainkamu.com" ]; then
    echo "Isi DOMAIN dan EMAIL di bagian atas $0 dulu."
    exit 1
fi
if [ "$(id -u)" -ne 0 ]; then
    echo "Jalankan sebagai root."
    exit 1
fi

echo "==> 1/7 Install paket server"
export DEBIAN_FRONTEND=noninteractive
apt-get update
apt-get install -y nginx mysql-server supervisor git unzip curl openssl certbot python3-certbot-nginx \
    php$PHP-fpm php$PHP-cli php$PHP-mysql php$PHP-mbstring php$PHP-xml php$PHP-curl \
    php$PHP-zip php$PHP-gd php$PHP-bcmath php$PHP-intl

if ! command -v composer >/dev/null 2>&1; then
    curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
fi

echo "==> 2/7 Batas upload PHP 12 MB"
for ini in /etc/php/$PHP/fpm/php.ini /etc/php/$PHP/cli/php.ini; do
    sed -i -E 's/^;?upload_max_filesize.*/upload_max_filesize = 12M/; s/^;?post_max_size.*/post_max_size = 13M/' "$ini"
done
systemctl restart php$PHP-fpm

echo "==> 3/7 Database dan .env"
cd "$APP_DIR"
if [ ! -f .env ]; then
    DB_PASS="$(openssl rand -hex 16)"
    mysql <<SQL
CREATE DATABASE IF NOT EXISTS \`$DB_NAME\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS '$DB_USER'@'localhost' IDENTIFIED BY '$DB_PASS';
ALTER USER '$DB_USER'@'localhost' IDENTIFIED BY '$DB_PASS';
GRANT ALL PRIVILEGES ON \`$DB_NAME\`.* TO '$DB_USER'@'localhost';
FLUSH PRIVILEGES;
SQL
    sed -e "s|__DOMAIN__|$DOMAIN|g" \
        -e "s|__DB_NAME__|$DB_NAME|g" \
        -e "s|__DB_USER__|$DB_USER|g" \
        -e "s|__DB_PASS__|$DB_PASS|g" \
        -e "s|__REVERB_ID__|$(shuf -i 100000-999999 -n 1)|g" \
        -e "s|__REVERB_KEY__|$(openssl rand -hex 10)|g" \
        -e "s|__REVERB_SECRET__|$(openssl rand -hex 20)|g" \
        -e "s|__DASHBOARD_PASS__|$(openssl rand -base64 12 | tr -d '/+=')|g" \
        deploy/env.production.example > .env
    echo "    .env dibuat."
else
    echo "    .env sudah ada, dilewati."
fi
chown root:www-data .env
chmod 640 .env

echo "==> 4/7 Install Laravel"
git config --global --add safe.directory "$APP_DIR"
composer install --no-dev --optimize-autoloader --no-interaction
mkdir -p storage/framework/{cache,sessions,views} storage/logs storage/app bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
grep -q '^APP_KEY=base64' .env || php artisan key:generate --force
bash deploy/deploy.sh --tanpa-pull

echo "==> 5/7 Nginx"
sed -e "s|__DOMAIN__|$DOMAIN|g" -e "s|__APP_DIR__|$APP_DIR|g" -e "s|__PHP__|$PHP|g" \
    deploy/nginx.conf > /etc/nginx/sites-available/gallery-app
ln -sf /etc/nginx/sites-available/gallery-app /etc/nginx/sites-enabled/gallery-app
rm -f /etc/nginx/sites-enabled/default
nginx -t
systemctl reload nginx

echo "==> 6/7 Supervisor (queue worker + Reverb)"
sed -e "s|__APP_DIR__|$APP_DIR|g" deploy/supervisor.conf > /etc/supervisor/conf.d/gallery-app.conf
supervisorctl reread
supervisorctl update
supervisorctl restart gallery-queue gallery-reverb || true

echo "==> 7/7 SSL (HTTPS)"
SSL="ok"
certbot --nginx -d "$DOMAIN" --non-interactive --agree-tos -m "$EMAIL" --redirect || SSL="gagal"

echo
echo "══════════════════════════════════════════════════════════"
echo " Setup selesai."
echo
echo " Login dashboard : $(grep '^DASHBOARD_USER=' .env | cut -d= -f2) / $(grep '^DASHBOARD_PASSWORD=' .env | cut -d= -f2)"
echo
echo " Langkah berikutnya:"
echo "   1. Isi bagian CLOUDINARY di .env:  nano $APP_DIR/.env"
echo "   2. Terapkan:                        bash $APP_DIR/deploy/deploy.sh"
echo "   3. Buka https://$DOMAIN/gallery"
if [ "$SSL" = "gagal" ]; then
    echo
    echo " PERHATIAN: SSL gagal dipasang. Pastikan domain $DOMAIN sudah"
    echo " mengarah ke IP VPS ini, lalu jalankan:"
    echo "   certbot --nginx -d $DOMAIN --redirect"
fi
echo "══════════════════════════════════════════════════════════"
