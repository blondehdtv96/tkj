#!/usr/bin/env bash
#
# Skrip pembaruan TKJ Learning di CT Proxmox.
# Jalankan sebagai root:  bash /var/www/tkj/deploy/update.sh
#
# Yang dilakukan: tarik kode terbaru, pasang dependensi, jalankan migrasi,
# bangun ulang aset, lalu segarkan cache. Database hanya disentuh oleh migrasi
# yang memang belum pernah dijalankan.

set -euo pipefail

APP_DIR="${APP_DIR:-/var/www/tkj}"
PHP_FPM_SERVICE="${PHP_FPM_SERVICE:-php8.3-fpm}"
WEB_USER="${WEB_USER:-www-data}"

# Composer menolak berjalan sebagai root tanpa penanda ini.
export COMPOSER_ALLOW_SUPERUSER=1

cd "$APP_DIR"

artisan() {
    # Artisan dijalankan sebagai pengguna web supaya berkas log dan cache yang
    # dibuatnya tetap bisa ditulis PHP-FPM.
    sudo -u "$WEB_USER" php artisan "$@"
}

echo "==> Menyalakan mode pemeliharaan"
artisan down --retry=60 || true

selesai() {
    echo "==> Mematikan mode pemeliharaan"
    artisan up || true
}
trap selesai EXIT

echo "==> Menarik kode terbaru dari origin/main"
git fetch --all --prune
# Berkas yang di-gitignore (.env, vendor, node_modules, public/build, storage) tidak ikut terhapus.
git reset --hard origin/main

echo "==> Memasang dependensi PHP"
composer install --no-dev --optimize-autoloader --no-interaction

echo "==> Membangun aset frontend"
npm ci
npm run build

echo "==> Menjalankan migrasi database"
artisan migrate --force

echo "==> Menyegarkan cache konfigurasi"
artisan config:cache
artisan route:cache
artisan view:cache
artisan storage:link || true

echo "==> Merapikan kepemilikan berkas"
chown -R "$WEB_USER":"$WEB_USER" "$APP_DIR"
chmod -R ug+rwX "$APP_DIR/storage" "$APP_DIR/bootstrap/cache"

echo "==> Memuat ulang PHP-FPM"
systemctl reload "$PHP_FPM_SERVICE"

echo "==> Selesai"
