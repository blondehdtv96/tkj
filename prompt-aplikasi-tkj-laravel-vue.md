# Prompt Aplikasi Web Pembelajaran TKJ (Laravel + Vue)

Buatkan aplikasi web pembelajaran TKJ (Teknik Komputer dan Jaringan) SMK untuk kelas 10, 11, dan 12 dengan backend **Laravel 11** dan frontend **Vue 3**.

---

## 1. Arsitektur

- Laravel 11 sebagai REST API (`routes/api.php`), Sanctum untuk autentikasi SPA berbasis token.
- Vue 3 Composition API + `<script setup>`, Vite, Vue Router, Pinia.
- Database MySQL. Tailwind CSS untuk styling. Axios untuk HTTP client.
- Struktur: Laravel di root, frontend Vue di folder `/resources/js` atau repo terpisah (jelaskan kedua opsi, pilih monorepo).

## 2. Role Pengguna

| Role  | Hak Akses |
|-------|-----------|
| Admin | Kelola user, kelas, mata pelajaran, laporan global |
| Guru  | Buat/edit materi, bank soal, kuis, lihat nilai siswa |
| Siswa | Akses materi, kerjakan kuis, lihat progres dan nilai |

## 3. Skema Database (migration + model + relasi)

- **users** — id, name, email, password, role, nis/nip, kelas_id
- **kelas** — id, tingkat [10/11/12], nama_rombel, wali_kelas_id
- **mata_pelajaran** — id, nama, tingkat, deskripsi
- **bab** — id, mata_pelajaran_id, judul, urutan
- **materi** — id, bab_id, judul, konten_html, tipe [teks/video/pdf], file_path, urutan
- **kuis** — id, bab_id, judul, durasi_menit, kkm, acak_soal, aktif
- **soal** — id, kuis_id, pertanyaan, tipe [pilihan_ganda/essay/benar_salah], gambar, pembahasan, bobot
- **opsi_jawaban** — id, soal_id, teks, is_benar
- **hasil_kuis** — id, user_id, kuis_id, skor, waktu_mulai, waktu_selesai
- **jawaban_siswa** — id, hasil_kuis_id, soal_id, opsi_id, jawaban_teks, is_benar
- **progres_materi** — id, user_id, materi_id, selesai_pada

## 4. Konten Materi (Seeder)

**Kelas 10:** K3LH, perakitan PC, instalasi OS Windows & Linux, media jaringan (UTP/fiber/wireless), alat ukur jaringan, model OSI & TCP/IP, topologi.

**Kelas 11:** IP address & subnetting VLSM/CIDR, VLAN & trunking, routing statis, RIP, OSPF, wireless 802.11, administrasi Linux server, keamanan jaringan dasar.

**Kelas 12:** DHCP, DNS, web server, FTP, mail, proxy, firewall, NAT, VPN, manajemen bandwidth & QoS, troubleshooting, monitoring jaringan.

Buat seeder dengan minimal 3 bab dan 10 soal per tingkat sebagai contoh.

## 5. Fitur Backend (Laravel)

- API Resource untuk semua response, Form Request untuk validasi.
- Policy/Gate untuk otorisasi per role.
- Endpoint CRUD: kelas, mata pelajaran, bab, materi, kuis, soal.
- Endpoint kuis: mulai kuis (server yang mengacak soal & mencatat waktu mulai), submit jawaban, koreksi otomatis di server. **Jangan pernah kirim kunci jawaban ke frontend sebelum kuis selesai.**
- Upload file materi (gambar/PDF/video) ke storage dengan validasi MIME dan ukuran.
- Endpoint statistik: rata-rata nilai per kelas, per bab, siswa di bawah KKM, progres penyelesaian materi.
- Export rekap nilai ke Excel (`maatwebsite/excel`).
- Seeder + factory untuk data dummy.
- PHPUnit/Pest test untuk endpoint kuis dan autentikasi.

## 6. Fitur Frontend (Vue)

- Layout terpisah: `AuthLayout`, `StudentLayout`, `TeacherLayout`, `AdminLayout`.
- Route guard berbasis role via Vue Router navigation guard.
- Pinia store: `authStore`, `materiStore`, `kuisStore`.
- **Halaman siswa:** dashboard progres, daftar bab, viewer materi, pengerjaan kuis dengan timer countdown dan auto-submit saat waktu habis, halaman hasil dengan pembahasan.
- **Halaman guru:** editor materi (rich text/WYSIWYG), form bank soal dengan opsi jawaban dinamis, tabel nilai siswa dengan filter dan sorting.
- Loading skeleton, toast notification, dan error handling terpusat di axios interceptor.
- Responsif mobile-first, dark mode, Tailwind dengan tema biru-teal.

### Komponen Interaktif Khusus TKJ

- **SubnetCalculator.vue** — hitung network, broadcast, range host, jumlah host dari input IP/prefix, plus mode latihan soal acak.
- **OsiLayerViewer.vue** — 7 layer OSI yang bisa diklik, menampilkan fungsi, protokol, dan perangkat per layer.
- **TopologyBuilder.vue** — kanvas drag-and-drop perangkat jaringan, hubungkan dengan kabel, validasi struktur topologi.
- **CliSimulator.vue** — terminal simulasi perintah Cisco/MikroTik dasar.

## 7. Output yang Diharapkan

1. Struktur folder lengkap.
2. File migration, model (beserta relasi Eloquent), controller, request, resource, policy.
3. `routes/api.php` lengkap.
4. Komponen Vue, router, dan store.
5. Perintah instalasi dan konfigurasi `.env`.
6. Penjelasan singkat alur autentikasi Sanctum SPA.

Kerjakan bertahap: mulai dari database dan API, baru frontend.

---

## Tips Penggunaan

Prompt ini besar untuk sekali jalan. Hasil terbaik didapat dengan memecahnya menjadi beberapa sesi:

1. Migration, model, dan seeder
2. Autentikasi Sanctum + role
3. API materi dan kuis
4. Frontend layout, router, dan store
5. Halaman siswa dan guru
6. Komponen simulator TKJ
