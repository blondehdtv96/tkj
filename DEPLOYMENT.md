# Panduan Deploy — CT Proxmox + Nginx + Cloudflare Tunnel

Runbook pemasangan TKJ Learning di LXC container Proxmox berbasis Debian/Ubuntu.

**Susunan yang dituju:**

```
Internet  ->  Cloudflare  ->  terowongan terenkripsi  ->  cloudflared (di CT)
                                                              |
                                                              v
                                                     nginx 127.0.0.1:80
                                                              |
                                                              v
                                                    PHP-FPM  ->  Laravel 11
                                                              |
                                                              v
                                                           MySQL
```

Tidak ada port yang perlu dibuka di router, dan TLS selesai di sisi Cloudflare.

Seluruh perintah dijalankan **sebagai root di dalam CT**, kecuali disebut lain. Ganti nilai berikut sesuai milik Anda:

| Penanda | Contoh | Keterangan |
|---------|--------|------------|
| `tkj.sekolah.sch.id` | domain Anda | Hostname yang dipakai di Cloudflare |
| `php8.3` | `php8.2` | Sesuaikan dengan versi PHP di CT |
| `KataSandiKuat` | — | Kata sandi pengguna MySQL |

> **Cek versi PHP lebih dulu**, karena nama paket dan path socket mengikutinya:
> ```bash
> php -v
> ```
> Jika CT memakai PHP 8.2, ganti setiap `php8.3` di panduan ini menjadi `php8.2`.

---

## 1. Paket sistem

PHP, Composer, Git, dan MySQL sudah Anda siapkan. Yang masih kurang: **nginx**, **PHP-FPM beserta ekstensinya**, dan **Node.js** (dibutuhkan karena `public/build` sengaja tidak ikut di-commit).

```bash
apt update

# Nginx
apt install -y nginx

# PHP-FPM dan ekstensi yang dibutuhkan Laravel 11 + maatwebsite/excel
apt install -y \
  php8.3-fpm php8.3-mysql php8.3-mbstring php8.3-xml \
  php8.3-curl php8.3-zip php8.3-gd php8.3-bcmath php8.3-intl

# Node.js 22 untuk membangun aset Vite
curl -fsSL https://deb.nodesource.com/setup_22.x | bash -
apt install -y nodejs

# Pastikan semuanya terbaca
php -m | grep -E '^(gd|zip|mysqli|mbstring|intl|bcmath)$'
node -v && npm -v && nginx -v
```

`gd` dan `zip` wajib ada — keduanya dipakai PhpSpreadsheet untuk export rekap nilai ke Excel.

---

## 2. Database MySQL

```bash
mysql -u root -p
```

```sql
CREATE DATABASE tkj_learning CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'tkj'@'localhost' IDENTIFIED BY 'KataSandiKuat';
GRANT ALL PRIVILEGES ON tkj_learning.* TO 'tkj'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

`utf8mb4` penting karena konten materi memuat teks HTML dan bisa mengandung emoji.

---

## 3. Ambil kode dari GitHub

```bash
mkdir -p /var/www
cd /var/www
git clone https://github.com/blondehdtv96/tkj.git
cd /var/www/tkj
```

> Jika repositorinya privat, pakai personal access token:
> `git clone https://<USERNAME>:<TOKEN>@github.com/blondehdtv96/tkj.git`

---

## 4. Berkas `.env`

```bash
cd /var/www/tkj
cp .env.example .env
nano .env
```

Isi dengan nilai berikut (bagian yang tidak disebut boleh dibiarkan bawaan):

```env
APP_NAME="TKJ Learning"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_TIMEZONE=Asia/Jakarta
APP_URL=https://tkj.sekolah.sch.id

APP_LOCALE=id
APP_FALLBACK_LOCALE=id
APP_FAKER_LOCALE=id_ID

LOG_CHANNEL=stack
LOG_LEVEL=warning

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=tkj_learning
DB_USERNAME=tkj
DB_PASSWORD=KataSandiKuat

SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_SECURE_COOKIE=true

CACHE_STORE=database
QUEUE_CONNECTION=sync
FILESYSTEM_DISK=local

VITE_APP_NAME="${APP_NAME}"
```

Catatan atas beberapa pilihan di atas:

- **`APP_DEBUG=false`** wajib di produksi. Nilai `true` menampilkan isi `.env` pada halaman error.
- **`APP_URL` memakai `https://`** karena Cloudflare yang menyelesaikan TLS.
- **`QUEUE_CONNECTION=sync`** dipakai karena aplikasi ini belum punya pekerjaan antrean, sehingga tidak perlu proses `queue:work` yang harus dijaga. Ubah ke `database` dan pasang worker hanya bila nanti ada job yang diantrekan.
- **`SESSION_SECURE_COOKIE=true`** aman dipakai karena pengunjung selalu masuk lewat HTTPS Cloudflare.

---

## 5. Dependensi, kunci aplikasi, dan migrasi

```bash
cd /var/www/tkj
export COMPOSER_ALLOW_SUPERUSER=1

composer install --no-dev --optimize-autoloader
php artisan key:generate
php artisan migrate --force
```

Mengisi data awal (kurikulum, katalog reward, soal pemahaman):

```bash
php artisan db:seed --force
```

> **Peringatan keamanan.** Seeder membuat akun dengan kata sandi seragam `password`, termasuk akun `admin`. Segera ganti setelah bisa login, atau lewati `db:seed` dan buat akun admin sendiri:
> ```bash
> php artisan tinker --execute="\App\Models\User::create(['name'=>'Administrator','email'=>'admin@sekolah.sch.id','username'=>'admin','role'=>'admin','password'=>bcrypt('GantiKataSandiIni')]);"
> ```
> Kalau memakai seeder, ganti kata sandi admin begitu selesai:
> ```bash
> php artisan tinker --execute="\$u=\App\Models\User::where('username','admin')->first(); \$u->password=bcrypt('KataSandiBaru'); \$u->save(); echo 'ok';"
> ```

---

## 6. Bangun aset frontend

`public/build` masuk `.gitignore`, jadi langkah ini **wajib** — tanpa ini halaman akan tampil kosong.

```bash
cd /var/www/tkj
npm ci
npm run build
```

Jika CT hanya punya RAM 512 MB dan proses build terhenti sendiri:

```bash
NODE_OPTIONS=--max-old-space-size=1024 npm run build
```

Bila tetap gagal, naikkan RAM CT sementara ke 2 GB lewat Proxmox, build, lalu turunkan lagi — aset hasil build tidak butuh RAM besar untuk dilayani.

---

## 7. Symlink storage dan izin berkas

```bash
cd /var/www/tkj
php artisan storage:link

chown -R www-data:www-data /var/www/tkj
chmod -R ug+rwX /var/www/tkj/storage /var/www/tkj/bootstrap/cache
```

`storage:link` membuat `public/storage` sehingga berkas materi dan gambar soal yang diunggah bisa diakses pengunjung.

---

## 8. Setelan PHP

Materi boleh diunggah sampai 50 MB (lihat `MateriRequest`), sedangkan bawaan PHP hanya 2 MB.

```bash
nano /etc/php/8.3/fpm/conf.d/99-tkj.ini
```

```ini
; Unggahan materi video/PDF sampai 50 MB, diberi kelonggaran menjadi 64 MB.
upload_max_filesize = 64M
post_max_size = 64M
max_file_uploads = 20

memory_limit = 256M
max_execution_time = 300

; OPcache untuk produksi.
opcache.enable = 1
opcache.memory_consumption = 128
opcache.max_accelerated_files = 10000
opcache.validate_timestamps = 0

date.timezone = Asia/Jakarta
```

```bash
systemctl restart php8.3-fpm
```

> `opcache.validate_timestamps = 0` membuat PHP tidak mengecek perubahan berkas, jadi lebih cepat — tetapi **setiap deploy wajib diakhiri `systemctl reload php8.3-fpm`**. Skrip `deploy/update.sh` sudah melakukannya.

---

## 9. Nginx

```bash
cp /var/www/tkj/deploy/nginx-tkj.conf /etc/nginx/sites-available/tkj
nano /etc/nginx/sites-available/tkj     # ganti server_name dan versi PHP bila perlu

ln -sf /etc/nginx/sites-available/tkj /etc/nginx/sites-enabled/tkj
rm -f /etc/nginx/sites-enabled/default

nginx -t
systemctl reload nginx
```

Uji dari dalam CT sebelum menyentuh Cloudflare:

```bash
curl -I -H 'Host: tkj.sekolah.sch.id' http://127.0.0.1/
curl -s -H 'Host: tkj.sekolah.sch.id' http://127.0.0.1/up
```

`/up` adalah health check bawaan Laravel dan harus menjawab `200`.

---

## 10. Cloudflare Tunnel

```bash
# Pasang cloudflared
curl -fsSL https://pkg.cloudflare.com/cloudflare-main.gpg \
  -o /usr/share/keyrings/cloudflare-main.gpg
echo 'deb [signed-by=/usr/share/keyrings/cloudflare-main.gpg] https://pkg.cloudflare.com/cloudflared any main' \
  > /etc/apt/sources.list.d/cloudflared.list
apt update && apt install -y cloudflared

# Login — perintah ini mencetak tautan yang dibuka di browser laptop Anda
cloudflared tunnel login

# Buat terowongan
cloudflared tunnel create tkj
```

Perintah `create` mencetak **Tunnel ID** dan lokasi berkas kredensial. Catat id tersebut.

```bash
mkdir -p /etc/cloudflared
cp /var/www/tkj/deploy/cloudflared-config.yml /etc/cloudflared/config.yml
nano /etc/cloudflared/config.yml     # isi <TUNNEL_ID> dan domain

# Pindahkan kredensial ke lokasi yang dirujuk config
cp ~/.cloudflared/<TUNNEL_ID>.json /etc/cloudflared/
chmod 600 /etc/cloudflared/<TUNNEL_ID>.json

# Arahkan DNS di Cloudflare ke terowongan ini
cloudflared tunnel route dns tkj tkj.sekolah.sch.id

# Jalankan sebagai layanan
cloudflared service install
systemctl enable --now cloudflared
systemctl status cloudflared --no-pager
```

Di dashboard Cloudflare, pastikan **SSL/TLS mode = Full** atau **Full (strict)**. Mode *Flexible* akan membuat perulangan pengalihan.

---

## 11. Verifikasi

```bash
# Dari dalam CT
curl -I -H 'Host: tkj.sekolah.sch.id' http://127.0.0.1/up

# Dari laptop
curl -I https://tkj.sekolah.sch.id/up
```

Lalu buka `https://tkj.sekolah.sch.id` dan periksa:

- [ ] Halaman login tampil dengan gaya (kalau polos, aset build belum jalan)
- [ ] Login admin berhasil
- [ ] Menu Materi, Bank Soal, Pengaturan Poin, Gamifikasi terbuka
- [ ] Siswa dapat membuka materi dan menyelesaikannya, poin bertambah
- [ ] Unggah berkas materi berukuran besar berhasil
- [ ] Export rekap nilai menghasilkan berkas `.xlsx`

Pantau log bila ada yang gagal:

```bash
tail -f /var/www/tkj/storage/logs/laravel.log
tail -f /var/log/nginx/tkj-error.log
journalctl -u cloudflared -f
```

---

## 12. Pembaruan berikutnya

Setelah ada perubahan di GitHub, cukup satu perintah:

```bash
bash /var/www/tkj/deploy/update.sh
```

Skrip itu menyalakan mode pemeliharaan, menarik `origin/main`, memasang dependensi, menjalankan migrasi, membangun ulang aset, menyegarkan cache, merapikan izin berkas, lalu memuat ulang PHP-FPM. Berkas yang di-gitignore (`.env`, `storage`, `public/build`) tidak tersentuh.

**Jangan pernah menjalankan `php artisan migrate:fresh` di server** — perintah itu mengosongkan seluruh tabel termasuk data siswa, nilai, dan poin.

---

## 13. Cadangan data

Materi yang diunggah berada di `storage/app/public`, jadi cadangannya harus mencakup database **dan** folder itu.

```bash
mkdir -p /var/backups/tkj

# Database
mysqldump -u tkj -p'KataSandiKuat' --single-transaction tkj_learning \
  | gzip > /var/backups/tkj/db-$(date +%F).sql.gz

# Berkas unggahan
tar czf /var/backups/tkj/storage-$(date +%F).tar.gz -C /var/www/tkj/storage/app public
```

Menjadwalkannya harian lewat cron:

```bash
crontab -e
```

```cron
0 2 * * * mysqldump -u tkj -p'KataSandiKuat' --single-transaction tkj_learning | gzip > /var/backups/tkj/db-$(date +\%F).sql.gz
15 2 * * * tar czf /var/backups/tkj/storage-$(date +\%F).tar.gz -C /var/www/tkj/storage/app public
30 2 * * * find /var/backups/tkj -type f -mtime +14 -delete
```

Di sisi Proxmox, aktifkan juga snapshot CT berkala sebagai lapis kedua.

---

## 14. Masalah yang sering muncul

| Gejala | Penyebab | Perbaikan |
|--------|----------|-----------|
| Halaman putih tanpa gaya | `npm run build` belum dijalankan | `cd /var/www/tkj && npm run build` |
| `500` di semua halaman | Izin `storage` salah atau `APP_KEY` kosong | `php artisan key:generate`, lalu `chown -R www-data:www-data storage bootstrap/cache` |
| `419 Page Expired` | Cache konfigurasi lama | `php artisan config:clear && php artisan config:cache` |
| `413 Request Entity Too Large` | `client_max_body_size` nginx atau `upload_max_filesize` PHP terlalu kecil | Ikuti langkah 8 dan 9 |
| Unggahan berhenti di ~100 MB | Batas Cloudflare paket gratis | Turunkan batas unggah materi, atau pakai paket berbayar |
| Perubahan kode tidak muncul | OPcache masih memegang berkas lama | `systemctl reload php8.3-fpm` |
| Log mencatat semua IP sebagai `127.0.0.1` | `trustProxies` belum aktif | Pastikan `bootstrap/app.php` versi terbaru sudah ter-pull |
| Terlalu banyak pengalihan | SSL/TLS mode Cloudflare = Flexible | Ubah ke Full di dashboard Cloudflare |
| Export Excel gagal | Ekstensi `gd` atau `zip` belum ada | `apt install php8.3-gd php8.3-zip && systemctl restart php8.3-fpm` |
