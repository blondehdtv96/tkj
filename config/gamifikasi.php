<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Nilai Poin
    |--------------------------------------------------------------------------
    |
    | Seluruh angka perolehan poin dikumpulkan di sini agar mudah disetel
    | tanpa menyentuh service. Nilai di sini hanyalah bawaan: admin dapat
    | menimpanya per materi (materi.poin) dan per soal (soal.poin).
    |
    | Poin kuis = jumlah poin soal yang dijawab benar. Soal troubleshooting
    | mendapat poin sebagian sesuai porsi langkah yang posisinya tepat.
    |
    */

    'poin' => [
        'materi_selesai' => 10,
        'soal_default' => 5,
        // Dipakai migrasi untuk mengisi poin soal lama, dan sebagai acuan
        // pembagian poin saat admin memakai tombol "bagi rata" di bank soal.
        'kuis_maksimal' => 50,
        'bonus_lulus_kkm' => 20,
        'bonus_tepat_waktu' => 15,
        'bonus_bab_selesai' => 50,
        'streak_per_hari' => 5,
        'streak_maksimal_hari' => 7,
    ],

    /*
    |--------------------------------------------------------------------------
    | Kuis Pemahaman Materi
    |--------------------------------------------------------------------------
    |
    | Persentase jawaban benar minimal agar materi boleh ditandai selesai.
    | Materi yang belum memiliki soal pemahaman tetap dapat ditandai selesai
    | secara langsung sehingga konten lama tidak ikut terkunci.
    |
    */

    'pemahaman' => [
        'minimal_benar_persen' => 100,
    ],

    /*
    |--------------------------------------------------------------------------
    | Batas Waktu Pengerjaan
    |--------------------------------------------------------------------------
    |
    | Bonus ketepatan waktu diberikan bila kuis diselesaikan dalam rasio durasi
    | berikut (0.75 = selesai sebelum 75% waktu terpakai).
    |
    */

    'ketepatan_waktu' => [
        'rasio_durasi' => 0.75,
    ],

    /*
    |--------------------------------------------------------------------------
    | Level Bertema TKJ
    |--------------------------------------------------------------------------
    |
    | Diurutkan menaik berdasarkan minimal_poin. Level tertinggi tidak memiliki
    | level berikutnya sehingga progres bar ditampilkan penuh.
    |
    */

    'level' => [
        ['nama' => 'Pemula', 'minimal_poin' => 0, 'ikon' => 'user'],
        ['nama' => 'Teknisi Junior', 'minimal_poin' => 500, 'ikon' => 'tool'],
        ['nama' => 'Network Specialist', 'minimal_poin' => 1500, 'ikon' => 'network'],
        ['nama' => 'Master Technician', 'minimal_poin' => 3000, 'ikon' => 'server'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Lencana Pencapaian
    |--------------------------------------------------------------------------
    |
    | Lencana dihitung ulang dari data (bukan disimpan) agar tidak pernah basi.
    | "metrik" merujuk ke kunci yang dihasilkan LencanaService::metrik().
    |
    */

    'lencana' => [
        ['kode' => 'langkah-pertama', 'nama' => 'Langkah Pertama', 'deskripsi' => 'Menyelesaikan materi pertama.', 'ikon' => 'check-circle', 'metrik' => 'materi_selesai', 'nilai' => 1],
        ['kode' => 'kutu-buku', 'nama' => 'Kutu Buku', 'deskripsi' => 'Menyelesaikan 10 materi.', 'ikon' => 'book', 'metrik' => 'materi_selesai', 'nilai' => 10],
        ['kode' => 'pelahap-modul', 'nama' => 'Pelahap Modul', 'deskripsi' => 'Menyelesaikan 25 materi.', 'ikon' => 'graduation-cap', 'metrik' => 'materi_selesai', 'nilai' => 25],
        ['kode' => 'lulus-perdana', 'nama' => 'Lulus Perdana', 'deskripsi' => 'Lulus KKM pada satu kuis.', 'ikon' => 'clipboard', 'metrik' => 'kuis_lulus', 'nilai' => 1],
        ['kode' => 'juru-kuis', 'nama' => 'Juru Kuis', 'deskripsi' => 'Lulus KKM pada 5 kuis.', 'ikon' => 'chart-bar', 'metrik' => 'kuis_lulus', 'nilai' => 5],
        ['kode' => 'nilai-sempurna', 'nama' => 'Nilai Sempurna', 'deskripsi' => 'Meraih skor 100 pada sebuah kuis.', 'ikon' => 'check', 'metrik' => 'nilai_sempurna', 'nilai' => 1],
        ['kode' => 'konsisten', 'nama' => 'Konsisten', 'deskripsi' => 'Belajar 7 hari berturut-turut.', 'ikon' => 'sun', 'metrik' => 'streak_hari', 'nilai' => 7],
        ['kode' => 'kolektor-poin', 'nama' => 'Kolektor Poin', 'deskripsi' => 'Mengumpulkan 1.000 poin.', 'ikon' => 'briefcase', 'metrik' => 'poin_seumur_hidup', 'nilai' => 1000],
        ['kode' => 'penukar-hadiah', 'nama' => 'Penukar Hadiah', 'deskripsi' => 'Menukar poin dengan reward.', 'ikon' => 'link', 'metrik' => 'reward_ditukar', 'nilai' => 1],
    ],

];
