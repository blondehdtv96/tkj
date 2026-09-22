<?php

namespace Database\Seeders;

use App\Models\Materi;
use App\Models\Reward;
use App\Models\SoalPemahaman;
use Illuminate\Database\Seeder;

class GamifikasiSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->rewards() as $reward) {
            Reward::updateOrCreate(['nama_barang' => $reward['nama_barang']], $reward);
        }

        $this->soalPemahaman();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function rewards(): array
    {
        return [
            [
                'nama_barang' => 'Pulpen Gel Teknik',
                'deskripsi' => 'Pulpen gel hitam 0.5 mm, cocok untuk mencatat konfigurasi jaringan.',
                'harga_poin' => 150,
                'stok' => 40,
            ],
            [
                'nama_barang' => 'Buku Catatan Jaringan',
                'deskripsi' => 'Buku tulis 80 halaman bersampul topologi jaringan.',
                'harga_poin' => 250,
                'stok' => 30,
            ],
            [
                'nama_barang' => 'Kabel UTP 3 Meter',
                'deskripsi' => 'Kabel UTP Cat6 lengkap dengan konektor RJ45 untuk praktik mandiri.',
                'harga_poin' => 450,
                'stok' => 20,
            ],
            [
                'nama_barang' => 'Tang Crimping',
                'deskripsi' => 'Tang crimping RJ45 standar praktik laboratorium TKJ.',
                'harga_poin' => 900,
                'stok' => 10,
            ],
            [
                'nama_barang' => 'Kaos Jurusan TKJ',
                'deskripsi' => 'Kaos merchandise jurusan, tersedia ukuran M sampai XL.',
                'harga_poin' => 1200,
                'stok' => 15,
            ],
            [
                'nama_barang' => 'Voucher Kantin Rp25.000',
                'deskripsi' => 'Voucher belanja di kantin sekolah, berlaku satu bulan sejak diambil.',
                'harga_poin' => 600,
                'stok' => 50,
            ],
            [
                'nama_barang' => 'USB Flashdisk 32GB',
                'deskripsi' => 'Flashdisk untuk menyimpan file ISO sistem operasi dan bahan praktik.',
                'harga_poin' => 1800,
                'stok' => 8,
            ],
        ];
    }

    /**
     * Soal pemahaman singkat untuk materi hasil CurriculumSeeder. Dicocokkan
     * berdasarkan judul bab agar tidak bergantung pada urutan id.
     */
    private function soalPemahaman(): void
    {
        foreach ($this->bankSoal() as $judulBab => $daftarSoal) {
            $materi = Materi::whereHas('bab', fn ($q) => $q->where('judul', $judulBab))
                ->orderBy('urutan')
                ->first();

            if (! $materi) {
                continue;
            }

            foreach ($daftarSoal as $urutan => $soal) {
                SoalPemahaman::updateOrCreate(
                    ['materi_id' => $materi->id, 'pertanyaan' => $soal['pertanyaan']],
                    [
                        'opsi' => $soal['opsi'],
                        'jawaban_benar' => $soal['jawaban_benar'],
                        'urutan' => $urutan + 1,
                    ]
                );
            }
        }
    }

    /**
     * @return array<string, array<int, array{pertanyaan: string, opsi: array<int, string>, jawaban_benar: int}>>
     */
    private function bankSoal(): array
    {
        return [
            'K3LH (Keselamatan dan Kesehatan Kerja serta Lingkungan Hidup)' => [
                [
                    'pertanyaan' => 'Apa fungsi utama gelang antistatis saat merakit PC?',
                    'opsi' => [
                        'Mencegah listrik statis merusak komponen',
                        'Mempercepat proses perakitan',
                        'Menahan panas dari prosesor',
                        'Menghubungkan PC ke jaringan',
                    ],
                    'jawaban_benar' => 0,
                ],
                [
                    'pertanyaan' => 'Huruf "LH" pada K3LH merujuk pada aspek apa?',
                    'opsi' => ['Lembaga Hukum', 'Lingkungan Hidup', 'Laporan Harian', 'Listrik dan Hardware'],
                    'jawaban_benar' => 1,
                ],
            ],
            'Perakitan PC' => [
                [
                    'pertanyaan' => 'Komponen mana yang harus dipasang lebih dulu ke motherboard sebelum motherboard masuk ke casing?',
                    'opsi' => ['Kartu grafis', 'Harddisk', 'Prosesor dan RAM', 'Power supply'],
                    'jawaban_benar' => 2,
                ],
            ],
            'IP Address dan Subnetting (VLSM/CIDR)' => [
                [
                    'pertanyaan' => 'Berapa jumlah host yang dapat dipakai pada jaringan dengan prefix /26?',
                    'opsi' => ['30 host', '62 host', '126 host', '254 host'],
                    'jawaban_benar' => 1,
                ],
                [
                    'pertanyaan' => 'Alamat 172.16.5.1 termasuk kelompok alamat apa?',
                    'opsi' => ['Publik', 'Privat', 'Loopback', 'Multicast'],
                    'jawaban_benar' => 1,
                ],
            ],
            'VLAN dan Trunking' => [
                [
                    'pertanyaan' => 'Port trunk pada switch berfungsi untuk apa?',
                    'opsi' => [
                        'Menghubungkan satu perangkat akhir saja',
                        'Membawa lalu lintas banyak VLAN antar switch',
                        'Memberikan IP address otomatis',
                        'Menyaring paket berdasarkan port layanan',
                    ],
                    'jawaban_benar' => 1,
                ],
            ],
            'DHCP dan DNS Server' => [
                [
                    'pertanyaan' => 'Layanan DNS bertugas menerjemahkan apa?',
                    'opsi' => [
                        'Nama domain menjadi alamat IP',
                        'Alamat MAC menjadi alamat IP',
                        'Alamat IP menjadi nomor port',
                        'Paket data menjadi frame',
                    ],
                    'jawaban_benar' => 0,
                ],
            ],
            'Firewall, NAT, dan VPN' => [
                [
                    'pertanyaan' => 'NAT umumnya dipakai untuk keperluan apa?',
                    'opsi' => [
                        'Mengenkripsi lalu lintas antar kantor',
                        'Menerjemahkan IP privat ke IP publik',
                        'Membagi bandwidth per pengguna',
                        'Menyimpan cache halaman web',
                    ],
                    'jawaban_benar' => 1,
                ],
            ],
        ];
    }
}
