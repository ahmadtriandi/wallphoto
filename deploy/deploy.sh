#!/usr/bin/env bash
# ══════════════════════════════════════════════════════════════════
#  Update aplikasi di VPS setelah push ke GitHub. Jalankan sebagai root:
#      bash /var/www/gallery-app/deploy/deploy.sh
# ══════════════════════════════════════════════════════════════════
set -euo pipefail
cd "$(dirname "$0")/.."

artisan() { sudo -u www-data php artisan "$@"; }

if [ "${1:-}" != "--tanpa-pull" ]; then
    echo "==> Ambil kode terbaru"
    git pull --ff-only
fi

echo "==> Composer"
composer install --no-dev --optimize-autoloader --no-interaction
chown -R www-data:www-data storage bootstrap/cache

echo "==> Database"
artisan migrate --force

echo "==> Cache konfigurasi dan tampilan"
artisan config:cache
artisan view:cache

echo "==> Restart queue worker dan Reverb"
artisan queue:restart
artisan reverb:restart || true

echo "Selesai."
