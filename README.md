# TKJ Learning — Aplikasi Web Pembelajaran Teknik Komputer dan Jaringan

Platform pembelajaran daring untuk jurusan **Teknik Komputer dan Jaringan (TKJ)** tingkat SMK kelas 10, 11, dan 12. Aplikasi menyediakan materi terstruktur per bab, kuis dengan koreksi otomatis di server, rekap nilai, serta sekumpulan alat bantu interaktif khas TKJ (kalkulator subnet, penjelajah layer OSI, perancang topologi, dan simulator CLI).

Dibangun sebagai **monorepo**: Laravel 11 sebagai REST API di root proyek, dan SPA Vue 3 di `resources/js` yang di-bundle oleh Vite.

---

## Daftar Isi

- [Fitur Utama](#fitur-utama)
- [Modul Gamifikasi](#modul-gamifikasi)
- [Teknologi](#teknologi)
- [Arsitektur](#arsitektur)
- [Struktur Folder](#struktur-folder)
- [Skema Database](#skema-database)
- [Alur Autentikasi Sanctum](#alur-autentikasi-sanctum)
- [Instalasi](#instalasi)
- [Akun Demo](#akun-demo)
- [Referensi API](#referensi-api)
- [Komponen Interaktif TKJ](#komponen-interaktif-tkj)
- [Pengujian](#pengujian)

---

## Fitur Utama

### Berdasarkan Role

| Role | Hak Akses |
|------|-----------|
| **Admin** | Kelola pengguna, kelas, mata pelajaran, referensi belajar, katalog reward, verifikasi penukaran poin, statistik global, dan export rekap nilai |
| **Guru** | Kelola materi, bank soal, kuis, soal pemahaman, katalog reward, verifikasi penukaran poin, serta melihat & memfilter nilai siswa |
| **Siswa** | Membaca materi, menandai progres, mengerjakan kuis, melihat nilai dan pembahasan, mengumpulkan poin, menukar reward, dan melihat papan peringkat |

### Alur Belajar Siswa

1. Memilih mata pelajaran sesuai tingkat, lalu membuka bab dan materinya.
2. Materi ditandai selesai setelah dibaca dan kuis pemahamannya dijawab benar (tersimpan di tabel `progres_materi`).
3. **Kuis terkunci sampai seluruh materi pada bab tersebut selesai** — divalidasi di server lewat `Kuis::materiBelumSelesai()`, bukan hanya di UI.
4. Saat kuis dimulai, server mengacak soal dan mencatat `waktu_mulai`. Frontend menjalankan timer countdown dengan auto-submit ketika waktu habis.
5. Jawaban dikoreksi otomatis di server, lalu siswa melihat skor beserta pembahasan tiap soal.

### Keamanan Kuis

- **Kunci jawaban tidak pernah dikirim ke frontend sebelum kuis selesai.** `SoalAttemptResource` hanya memuat teks opsi tanpa flag `is_benar`; pembahasan baru ikut pada response hasil.
- Penilaian sepenuhnya dihitung di server (`KuisAttemptController::submit`) di dalam transaksi database.
- Submit bersifat **idempoten** — mengirim ulang pada hasil kuis yang sudah selesai hanya mengembalikan hasil yang tersimpan, tidak menghitung ulang skor.
- Memulai kuis yang sudah berjalan akan melanjutkan attempt yang sama, bukan membuat attempt baru.
- Soal essay tidak dihitung dalam skor otomatis (`is_benar` bernilai `null`, bobotnya dikecualikan dari pembagi).

### Fitur Lain

- **Statistik** — rata-rata nilai per kelas dan per bab, daftar siswa di bawah KKM, serta persentase penyelesaian materi per rombel.
- **Export Excel** — rekap nilai (`maatwebsite/excel`) dengan filter kelas dan kuis, termasuk kolom status Lulus/Belum Lulus terhadap KKM.
- **Referensi Belajar** — satu paket pendamping per bab berisi ringkasan materi, video YouTube, dan soal studi kasus.
- **Upload file materi** ke `storage/app/public` dengan validasi tipe dan ukuran; file lama dihapus saat diganti.
- **Dark mode** (Tailwind `darkMode: 'class'`), tema biru–teal, layout responsif mobile-first.
- **Error handling terpusat** di axios interceptor: 401 membersihkan token dan redirect ke login, 422 diteruskan ke form, sisanya ditampilkan sebagai toast.

---

## Modul Gamifikasi

Siswa mengumpulkan poin dari aktivitas belajar, naik level bertema TKJ, dan menukarkan poin dengan barang fisik yang disediakan sekolah melalui verifikasi guru/admin.

### Perolehan Poin

Seluruh angka disetel di `config/gamifikasi.php` tanpa menyentuh kode service.

| Sumber | Poin | Catatan |
|--------|------|---------|
| Materi selesai | `materi.poin` (bawaan 10) | Diberikan setelah kuis pemahaman dijawab benar |
| Kuis | jumlah `soal.poin` yang dijawab benar (bawaan 5/soal) | Troubleshooting menyumbang sebagian sesuai porsi langkah tepat |
| Bonus lulus KKM | 20 | Sekali per kuis |
| Bonus ketepatan waktu | 15 | Kuis selesai dalam ≤75% durasi **dan** skor di atas nol |
| Bonus bab tuntas | 50 | Ketika seluruh materi pada satu bab selesai |
| Bonus streak harian | 5 × hari (maks. 7) | Dicatat otomatis saat login |
| Penyesuaian manual | bebas | Guru/admin, wajib menyertakan keterangan |

Poin kuis dan bonusnya dikunci **per kuis, bukan per percobaan**, sehingga mengulang kuis yang sama tidak menambah poin lagi. Soal essay tidak dinilai server sehingga tidak menyumbang poin otomatis.

Setiap mutasi menulis satu baris `points_log` sekaligus menyesuaikan `users.total_poin` dalam satu transaksi, sehingga saldo tidak pernah menyimpang dari riwayatnya.

### Anti Asal-Klik

Materi dapat memiliki **soal pemahaman singkat** (`soal_pemahaman`). Selama soal itu ada, `POST /api/materi/{materi}/selesai` menolak permintaan tanpa jawaban atau dengan jawaban salah, dan kunci jawaban tidak pernah dikirim ke siswa. Materi tanpa soal pemahaman tetap bisa ditandai selesai secara langsung, jadi konten lama tidak ikut terkunci.

Membuka materi hanya mencatat progres berstatus `dibaca`; status `selesai` — yang membuka kunci kuis bab dan menghasilkan poin — baru ditulis setelah pemeriksaan di atas lolos.

### Tipe Soal

| Tipe | Cara menjawab | Penilaian |
|------|---------------|-----------|
| `pilihan_ganda` | Pilih satu opsi | Otomatis |
| `benar_salah` | Pilih Benar atau Salah | Otomatis |
| `troubleshooting` | Menyusun langkah penanganan sesuai urutan | Otomatis, dengan nilai sebagian per posisi tepat |
| `essay` | Uraian bebas | Tidak dinilai server |

**Soal troubleshooting** memuat skenario kasus dan daftar langkah. Guru/admin memasukkan langkah dalam urutan yang benar (tersimpan di `opsi_jawaban.urutan`); siswa menerimanya **dalam keadaan teracak** — `SoalAttemptResource` mengacak langkah dan tidak pernah mengirim kolom `urutan` maupun `is_benar`. Nilai dan poin dihitung dari jumlah posisi yang tepat: menempatkan 3 dari 4 langkah dengan benar menghasilkan 75% dari bobot dan 75% dari poin soal.

### Pengaturan Poin oleh Admin

Poin bawaan ada di `config/gamifikasi.php`, tetapi guru dan admin dapat menimpanya per item:

- **Di form penulisan konten** — kolom "Poin gamifikasi" pada form materi dan form soal, di samping kolom bobot nilai.
- **Di halaman Pengaturan Poin** (`/admin/poin` dan `/guru/poin`) — seluruh materi dan soal satu bab dalam satu tabel, lengkap dengan total poin bab, tombol *bagi rata* poin maksimal kuis ke seluruh soalnya, dan tombol kembalikan ke bawaan.

Mengosongkan kolom poin berarti item itu kembali memakai nilai bawaan. Bobot nilai dan poin gamifikasi terpisah: **bobot** menentukan skor kuis, **poin** menentukan perolehan gamifikasi.

### Idempotensi

Kolom `points_log.kunci_unik` (unik bersama `user_id`) membuat perolehan poin tidak bisa digandakan: `materi:12`, `kuis:5`, `bonus_bab:3`, `streak:2026-09-22`, dan seterusnya. Menandai materi selesai dua kali, submit ulang kuis, maupun mengulang kuis dari awal tidak menambah poin untuk kedua kalinya.

### Level dan Lencana

| Level | Minimal Poin |
|-------|--------------|
| Pemula | 0 |
| Teknisi Junior | 500 |
| Network Specialist | 1.500 |
| Master Technician | 3.000 |

Lencana (`Langkah Pertama`, `Kutu Buku`, `Nilai Sempurna`, `Konsisten`, dan lainnya) **dihitung ulang dari data setiap kali diminta**, bukan disimpan, sehingga tidak pernah basi ketika nilai atau progres berubah.

### Leaderboard

Peringkat dihitung dari `points_log` pada rentang periode berjalan — bukan dari saldo `total_poin` — supaya tidak terpengaruh penukaran reward.

- **Periode**: mingguan, bulanan, semester (ganjil mulai Juli, genap mulai Januari)
- **Lingkup**: per kelas, per angkatan (tingkat), atau seluruh sekolah

### Alur Penukaran Reward

1. Siswa mengajukan penukaran. **Poin langsung ditahan** dan stok reward berkurang saat itu juga, sehingga poin yang sama tidak bisa dibelanjakan dua kali selagi menunggu verifikasi.
2. Guru/admin menyetujui (`approved`), menandai sudah diambil (`completed`), atau menolak (`rejected` — wajib menyertakan alasan).
3. Penolakan mengembalikan poin siswa dan memulihkan stok. Pengajuan yang sudah diproses tidak dapat diproses ulang.
4. Reward yang sudah pernah ditukar tidak bisa dihapus, hanya dinonaktifkan, agar riwayat penukaran tetap terbaca.

### Event

`PointsEarned` dan `RedemptionApproved` dipancarkan dari service sebagai titik sambung notifikasi, tanpa menambah beban pada logika poin.

### Catatan Integrasi

Tabel `material_progress` tidak dibuat baru — perannya sudah dipegang `progres_materi` milik aplikasi, yang dipakai kembali dan hanya ditambahi kolom `status`. Membuat tabel kembar akan memecah sumber kebenaran progres dan mematahkan penguncian kuis yang sudah berjalan.

---

## Teknologi

**Backend**
- PHP 8.2+, Laravel 11
- Laravel Sanctum 4 (autentikasi SPA berbasis token)
- maatwebsite/excel 3.1
- MySQL
- PHPUnit 11

**Frontend**
- Vue 3 (Composition API, `<script setup>`)
- Vue Router 4 + Pinia 2
- Vite 6, Tailwind CSS 3 (+ plugin `forms` dan `typography`)
- Tiptap (editor WYSIWYG untuk materi)
- Axios

---

## Arsitektur

```
Browser (SPA Vue 3)
   |  Bearer token di header Authorization
   v
routes/api.php -- middleware auth:sanctum -- middleware role:*
   v
Controller (Api/*) -- FormRequest (validasi) -- Model Eloquent
   v
API Resource (Http/Resources/*) -- JSON response
```

- Semua route non-API ditangkap `routes/web.php` (`/{any}`) dan mengembalikan `resources/views/app.blade.php`, sehingga Vue Router yang memegang history routing.
- Otorisasi ditegakkan middleware `role` (`App\Http\Middleware\EnsureRole`), didaftarkan sebagai alias di `bootstrap/app.php`. Middleware menerima daftar role variadic, misalnya `role:guru,admin`.
- Setiap response API melewati API Resource, setiap input menulis melewati Form Request.

---

## Struktur Folder

```
app/
├── Events/                          # PointsEarned, RedemptionApproved
├── Exports/NilaiExport.php          # Export rekap nilai ke Excel
├── Http/
│   ├── Controllers/Api/             # 18 controller REST API
│   ├── Middleware/EnsureRole.php    # Otorisasi berbasis role
│   ├── Requests/                    # Validasi input (Bab, Kelas, Kuis, Materi, Soal, User,
│   │                                # Reward, Redemption, PenyesuaianPoin, ...)
│   └── Resources/                   # Bentuk JSON response
├── Models/                          # Bab, HasilKuis, JawabanSiswa, Kelas, Kuis,
│                                    # MataPelajaran, Materi, OpsiJawaban, PointsLog,
│                                    # ProgresMateri, Redemption, ReferensiBelajar,
│                                    # Reward, Soal, SoalPemahaman, User
└── Services/Gamifikasi/             # PoinService, LevelService, LencanaService,
                                     # LeaderboardService, RedeemService, PemahamanService

database/
├── migrations/                      # Skema tabel
└── seeders/
    ├── UserSeeder.php               # Admin, guru/wali kelas, dan siswa per tingkat
    ├── CurriculumSeeder.php         # 3 mapel x 3 bab, materi, kuis, dan soal
    └── ReferensiBelajarSeeder.php   # Ringkasan, video, dan studi kasus per bab

resources/js/
├── components/
│   ├── tkj/                         # SubnetCalculator, OsiLayerViewer,
│   │                                # TopologyBuilder, CliSimulator
│   ├── gamifikasi/                  # LevelProgress, BadgeGrid, PoinPopup,
│   │                                # PemahamanQuiz
│   ├── RichTextEditor.vue           # Editor Tiptap untuk konten materi
│   ├── ReferensiBelajarPanel.vue
│   └── ConfirmModal.vue, ToastContainer.vue, StatCard.vue, SkeletonBlock.vue, Icon.vue
├── layouts/                         # AuthLayout, StudentLayout, TeacherLayout,
│                                    # AdminLayout, AppShell
├── pages/
│   ├── auth/Login.vue
│   ├── student/                     # Dashboard, MataPelajaranList/Detail, MateriViewer,
│   │                                # KuisAttempt, KuisResult, NilaiList,
│   │                                # PoinDashboard, RewardKatalog
│   ├── teacher/                     # Dashboard, MateriManage, BankSoalManage, NilaiSiswa
│   ├── admin/                       # Dashboard, Users, Kelas, MataPelajaran,
│   │                                # Referensi, Laporan
│   └── shared/                      # ToolsHub, Leaderboard, GamifikasiManage,
│                                    # PoinKonten
├── router/index.js                  # Route + navigation guard berbasis role
├── stores/                          # auth, materi, kuis, gamifikasi, theme, toast, confirm
└── bootstrap.js                     # Konfigurasi axios & interceptor error

routes/api.php                       # Seluruh endpoint REST
tests/Feature/                       # AuthTest, KuisTest
```

---

## Skema Database

| Tabel | Kolom Utama | Relasi |
|-------|-------------|--------|
| `users` | name, email, password, role (`admin`/`guru`/`siswa`), nis, nip, username, kelas_id, **total_poin, level, streak_hari, streak_terakhir** | `belongsTo` kelas, `hasMany` points_log & redemptions |
| `kelas` | tingkat (10/11/12), nama_rombel, wali_kelas_id | `hasMany` siswa, `belongsTo` wali kelas |
| `mata_pelajaran` | nama, tingkat, deskripsi | `hasMany` bab |
| `bab` | mata_pelajaran_id, judul, urutan | `hasMany` materi & kuis, `hasOne` referensi_belajar |
| `materi` | bab_id, judul, konten_html, tipe (`teks`/`video`/`pdf`), file_path, urutan, poin | `belongsTo` bab |
| `kuis` | bab_id, judul, durasi_menit, kkm, acak_soal, aktif | `hasMany` soal & hasil_kuis |
| `soal` | kuis_id, pertanyaan, skenario, tipe (`pilihan_ganda`/`essay`/`benar_salah`/`troubleshooting`), gambar, pembahasan, bobot, poin | `hasMany` opsi_jawaban |
| `opsi_jawaban` | soal_id, teks, is_benar, urutan (kunci langkah troubleshooting) | `belongsTo` soal |
| `hasil_kuis` | user_id, kuis_id, skor, waktu_mulai, waktu_selesai | `hasMany` jawaban_siswa |
| `jawaban_siswa` | hasil_kuis_id, soal_id, opsi_id, jawaban_teks, jawaban_urutan, is_benar, porsi_benar | `belongsTo` hasil_kuis, soal, opsi |
| `progres_materi` | user_id, materi_id, status (`dibaca`/`selesai`), selesai_pada — unik per pasangan user+materi | `belongsTo` user, materi |
| `referensi_belajar` | bab_id (unik), ringkasan, video_judul, video_url, kasus_soal | `belongsTo` bab |
| `soal_pemahaman` | materi_id, pertanyaan, opsi (JSON), jawaban_benar, urutan | `belongsTo` materi |
| `points_log` | user_id, sumber, jumlah (bertanda), keterangan, kunci_unik — unik per pasangan user+kunci | `belongsTo` user |
| `rewards` | nama_barang, deskripsi, harga_poin, stok, gambar, aktif | `hasMany` redemptions |
| `redemptions` | user_id, reward_id, jumlah_poin, status (`pending`/`approved`/`completed`/`rejected`), catatan_siswa, catatan_petugas, diproses_oleh, diproses_pada | `belongsTo` user, reward, petugas |

Seluruh foreign key konten menggunakan `cascadeOnDelete`, sedangkan relasi opsional (`kelas_id`, `wali_kelas_id`, `opsi_id`) memakai `nullOnDelete`.

---

## Alur Autentikasi Sanctum

Aplikasi memakai **Sanctum mode API token**, bukan cookie SPA — frontend dan backend dilayani dari origin yang sama namun request tetap stateless.

1. Pengguna mengirim `POST /api/login` berisi `identifier` dan `password`. `identifier` dicocokkan ke kolom `username` (admin & guru) atau `nis` (siswa).
2. Bila cocok, server menerbitkan token lewat `$user->createToken('spa-token')` dan mengembalikan `{ user, token }`.
3. Frontend menyimpan token dan data user di `localStorage`, lalu memasang header `Authorization: Bearer <token>` sebagai default axios.
4. Setiap request berikutnya melewati middleware `auth:sanctum`, dilanjutkan middleware `role` untuk endpoint yang dibatasi.
5. `POST /api/logout` menghapus token yang sedang dipakai (`currentAccessToken()->delete()`).
6. Response `401` apa pun otomatis membersihkan `localStorage` dan mengarahkan pengguna ke `/login` melalui axios interceptor.

Navigation guard Vue Router membaca `meta.roles` tiap route; pengguna yang tidak berwenang dialihkan ke dashboard sesuai rolenya.

---

## Instalasi

### Prasyarat

- PHP 8.2+ dengan ekstensi `gd` atau `imagick` (dibutuhkan `maatwebsite/excel`)
- Composer 2
- Node.js 18+ dan npm
- MySQL 8 / MariaDB (XAMPP sudah mencukupi)

### Langkah

```bash
# 1. Install dependency
composer install
npm install

# 2. Siapkan environment
cp .env.example .env
php artisan key:generate

# 3. Buat database terlebih dahulu
#    CREATE DATABASE tkj_learning;
```

Konfigurasi `.env` yang perlu disesuaikan:

```env
APP_NAME="TKJ Learning"
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=tkj_learning
DB_USERNAME=root
DB_PASSWORD=

FILESYSTEM_DISK=local
```

```bash
# 4. Migrasi dan isi data contoh
php artisan migrate --seed

# 5. Symlink storage agar file materi dapat diakses publik
php artisan storage:link

# 6. Jalankan aplikasi
php artisan serve      # http://127.0.0.1:8000
npm run dev            # Vite dev server (terminal terpisah)
```

Alternatif satu perintah untuk pengembangan (server + queue + log + vite):

```bash
composer run dev
```

Untuk produksi:

```bash
npm run build
php artisan config:cache
php artisan route:cache
```

> Jika di-hosting lewat XAMPP, arahkan DocumentRoot ke folder `public/`.

---

## Akun Demo

Seeder membuat satu admin, satu guru wali kelas per tingkat, satu guru produktif, dan 5 siswa per rombel (X TKJ 1, XI TKJ 1, XII TKJ 1). **Password semua akun hasil seeder: `password`.**

| Role | Login dengan | Contoh |
|------|--------------|--------|
| Admin | username | `admin` |
| Guru (wali kelas) | username | `wali.tkj10`, `wali.tkj11`, `wali.tkj12` |
| Guru produktif | username | `guru.produktif` |
| Siswa | NIS | NIS acak hasil factory — lihat tabel `users` |

NIS siswa dibangkitkan acak oleh factory, jadi ambil nilainya dari database:

```bash
php artisan tinker --execute="App\Models\User::where('role','siswa')->get()->each(fn(\$u)=>print(\$u->name.' | '.\$u->nis.PHP_EOL));"
```

---

## Referensi API

Base URL: `/api`. Seluruh endpoint kecuali `POST /login` memerlukan header `Authorization: Bearer <token>`.

### Autentikasi

| Method | Endpoint | Akses |
|--------|----------|-------|
| POST | `/login` | Publik |
| GET | `/user` | Semua role |
| POST | `/logout` | Semua role |

### Konten Pembelajaran (baca)

| Method | Endpoint | Akses |
|--------|----------|-------|
| GET | `/mata-pelajaran`, `/mata-pelajaran/{id}` | Semua role |
| GET | `/bab`, `/bab/{id}` | Semua role |
| GET | `/materi`, `/materi/{id}` | Semua role |
| GET | `/kuis` | Semua role (menandai status terkunci untuk siswa) |
| GET | `/kelas` | Semua role |
| GET | `/referensi-belajar` | Semua role |
| GET | `/soal-pemahaman?materi_id=` | Semua role (kunci jawaban hanya untuk guru/admin) |

### Gamifikasi

| Method | Endpoint | Akses |
|--------|----------|-------|
| GET | `/gamifikasi/ringkasan` | Semua role (guru/admin boleh `?user_id=`) |
| GET | `/gamifikasi/poin` | Semua role (siswa hanya miliknya) |
| GET | `/gamifikasi/leaderboard?periode=&lingkup=` | Semua role |
| GET | `/gamifikasi/rewards` | Semua role (siswa hanya reward aktif) |
| GET | `/gamifikasi/redemptions`, `/gamifikasi/redemptions/{id}` | Semua role (siswa hanya miliknya) |
| POST | `/gamifikasi/redemptions` | Siswa — mengajukan penukaran |
| POST | `/gamifikasi/rewards`, `/gamifikasi/rewards/{id}` | Guru & admin |
| DELETE | `/gamifikasi/rewards/{id}` | Guru & admin |
| POST | `/gamifikasi/redemptions/{id}/status` | Guru & admin — approve/complete/reject |
| POST | `/gamifikasi/poin/penyesuaian` | Guru & admin |
| POST / PUT / DELETE | `/soal-pemahaman` | Guru & admin |
| GET / POST | `/poin-konten?bab_id=` | Guru & admin — pengaturan poin materi & soal satu bab |

### Siswa

| Method | Endpoint | Keterangan |
|--------|----------|------------|
| POST | `/materi/{materi}/selesai` | Tandai materi selesai |
| POST | `/kuis/{kuis}/mulai` | Mulai/lanjutkan attempt, soal diacak server |
| POST | `/hasil-kuis/{hasilKuis}/submit` | Submit jawaban, dikoreksi otomatis |

### Nilai

| Method | Endpoint | Keterangan |
|--------|----------|------------|
| GET | `/hasil-kuis` | Siswa melihat miliknya sendiri; guru/admin melihat & memfilter semua |
| GET | `/hasil-kuis/{hasilKuis}` | Detail beserta jawaban dan pembahasan |

### Guru & Admin

| Method | Endpoint |
|--------|----------|
| GET | `/kuis/{kuis}` |
| GET, POST, POST `{id}`, DELETE | `/soal` |
| POST, PUT, DELETE | `/mata-pelajaran` |
| POST, PUT, DELETE | `/bab` |
| POST, POST `{id}`, DELETE | `/materi` (update memakai POST karena mengirim multipart file) |
| POST, PUT, DELETE | `/kuis` |
| GET | `/statistik` |
| GET | `/laporan/export-nilai?kelas_id=&kuis_id=` |

### Admin

| Method | Endpoint |
|--------|----------|
| POST, PUT, DELETE | `/kelas` |
| GET, POST, PUT, DELETE | `/users` |
| POST, DELETE | `/referensi-belajar` |

---

## Komponen Interaktif TKJ

Diakses siswa melalui menu **Tools** (`/siswa/tools`), seluruhnya berjalan di sisi klien tanpa request ke server.

| Komponen | Fungsi |
|----------|--------|
| **SubnetCalculator.vue** | Menghitung network address, broadcast, range host, jumlah host, subnet mask, dan kelas IP dari input IP/prefix. Menandai IP privat serta menyediakan tabel referensi CIDR. |
| **OsiLayerViewer.vue** | Tujuh layer OSI yang dapat diklik, lengkap dengan deskripsi, analogi sehari-hari, daftar protokol beserta nomor port, dan perangkat tiap layer. |
| **TopologyBuilder.vue** | Kanvas SVG untuk menambahkan perangkat (PC, switch, router, server, access point), menggesernya, serta menghubungkannya dengan kabel pada mode connect. |
| **CliSimulator.vue** | Terminal simulasi dua mode: Cisco IOS dan MikroTik RouterOS. Mendukung perintah dasar seperti konfigurasi IP address, DHCP pool, dan perintah `show`, dengan riwayat perintah dan `help`. |

---

## Pengujian

```bash
php artisan test
```

Cakupan `tests/Feature`:

**AuthTest**
- Login siswa dengan NIS dan guru dengan username
- Penolakan login dengan password salah
- Endpoint terproteksi menolak request tanpa token
- Pengambilan profil dan proses logout

**KuisTest**
- Response `mulai` kuis tidak memuat kunci jawaban
- Koreksi otomatis skor di server saat submit
- Sifat idempoten pada submit ulang hasil kuis yang sudah selesai
- Kuis terkunci sebelum seluruh materi bab diselesaikan, dan terbuka setelahnya
- Daftar kuis menandai status terkunci bagi siswa
- Siswa tidak dapat mengelola bank soal
- Guru dapat membuat soal dengan opsi jawaban dinamis

**GamifikasiTest**
- Poin materi beserta bonus bab tuntas, dan idempotensinya saat ditandai dua kali
- Kuis pemahaman menolak penandaan selesai tanpa jawaban atau dengan jawaban salah
- Kunci soal pemahaman tidak dikirim ke siswa, tetapi terlihat oleh guru
- Sekadar membuka materi tidak membuka kunci kuis
- Poin kuis, bonus KKM, dan bonus ketepatan waktu saat submit
- Kenaikan level mengikuti total poin, dan siswa tidak bisa menyesuaikan poin sendiri
- Penukaran menahan poin serta mengurangi stok; ditolak bila poin kurang atau stok habis
- Persetujuan oleh guru, penolakan yang mengembalikan poin dan stok, alasan penolakan wajib diisi
- Pengajuan yang sudah diproses tidak dapat diproses ulang
- Siswa hanya melihat riwayat poin dan penukaran miliknya, serta katalog reward aktif
- Leaderboard terurut berdasarkan perolehan periode
- Bonus streak login hanya diberikan sekali per hari

**SoalTroubleshootingTest**
- Admin dapat membuat soal pilihan ganda, benar/salah, essay, dan troubleshooting
- Nomor urutan langkah wajib lengkap dan berurutan 1..n tanpa pengulangan
- Siswa tidak menerima kunci urutan maupun penanda benar pada langkah
- Urutan benar seluruhnya memberi skor dan poin penuh
- Urutan sebagian benar memberi skor dan poin sebagian
- Soal troubleshooting yang tidak dijawab bernilai nol
- Mengulang kuis tidak menggandakan poin
- Poin soal dan poin materi mengikuti pengaturan admin, dan kembali ke bawaan bila dikosongkan
- Halaman pengaturan poin hanya dapat diakses guru dan admin

Konfigurasi pengujian berada di `phpunit.xml`.

---

## Catatan Konten Seeder

`CurriculumSeeder` membuat tiga mata pelajaran (satu per tingkat), masing-masing dengan 3 bab, satu materi pengantar, satu kuis (durasi 20 menit, KKM 75, soal diacak), dan beberapa soal pilihan ganda berpembahasan.

- **Kelas 10** — K3LH, Perakitan PC, Instalasi Sistem Operasi (Windows & Linux)
- **Kelas 11** — IP Address dan Subnetting (VLSM/CIDR), VLAN dan Trunking, Routing Statis/RIP/OSPF
- **Kelas 12** — DHCP dan DNS Server, Web Server/FTP/Mail Server, Firewall/NAT/VPN

`ReferensiBelajarSeeder` melengkapi tiap bab dengan ringkasan materi, tautan video YouTube, dan soal studi kasus.

`GamifikasiSeeder` mengisi katalog reward contoh (alat tulis, kabel UTP, tang crimping, merchandise, voucher kantin) beserta soal pemahaman singkat untuk sebagian materi.
