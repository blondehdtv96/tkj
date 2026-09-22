<?php

namespace Database\Seeders;

use App\Models\Bab;
use App\Models\Kuis;
use App\Models\MataPelajaran;
use App\Models\Materi;
use App\Models\OpsiJawaban;
use App\Models\Soal;
use Illuminate\Database\Seeder;

class CurriculumSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->curriculum() as $tingkat => $data) {
            $mapel = MataPelajaran::create([
                'nama' => "Teknik Komputer dan Jaringan - Kelas {$tingkat}",
                'tingkat' => $tingkat,
                'deskripsi' => $data['deskripsi'],
            ]);

            foreach ($data['bab'] as $urutanBab => $babData) {
                $urutanBab++;

                $bab = Bab::create([
                    'mata_pelajaran_id' => $mapel->id,
                    'judul' => $babData['judul'],
                    'urutan' => $urutanBab,
                ]);

                Materi::create([
                    'bab_id' => $bab->id,
                    'judul' => "Pengantar {$babData['judul']}",
                    'konten_html' => "<p>{$babData['materi']}</p>",
                    'tipe' => 'teks',
                    'urutan' => 1,
                ]);

                $kuis = Kuis::create([
                    'bab_id' => $bab->id,
                    'judul' => "Kuis {$babData['judul']}",
                    'durasi_menit' => 20,
                    'kkm' => 75,
                    'acak_soal' => true,
                    'aktif' => true,
                ]);

                foreach ($babData['soal'] as $soalData) {
                    $soal = Soal::create([
                        'kuis_id' => $kuis->id,
                        'pertanyaan' => $soalData['pertanyaan'],
                        'tipe' => 'pilihan_ganda',
                        'pembahasan' => $soalData['pembahasan'],
                        'bobot' => 10,
                    ]);

                    foreach ($soalData['opsi'] as $teks => $isBenar) {
                        OpsiJawaban::create([
                            'soal_id' => $soal->id,
                            'teks' => $teks,
                            'is_benar' => $isBenar,
                        ]);
                    }
                }
            }
        }
    }

    private function curriculum(): array
    {
        return [
            10 => [
                'deskripsi' => 'Dasar-dasar K3LH, perakitan komputer, dan instalasi sistem operasi untuk kelas 10.',
                'bab' => [
                    [
                        'judul' => 'K3LH (Keselamatan dan Kesehatan Kerja serta Lingkungan Hidup)',
                        'materi' => 'K3LH adalah upaya untuk menjamin keselamatan, kesehatan kerja, dan kelestarian lingkungan saat bekerja di laboratorium komputer dan jaringan.',
                        'soal' => [
                            [
                                'pertanyaan' => 'Apa kepanjangan dari K3LH?',
                                'pembahasan' => 'K3LH adalah singkatan dari Keselamatan dan Kesehatan Kerja serta Lingkungan Hidup.',
                                'opsi' => [
                                    'Keselamatan dan Kesehatan Kerja serta Lingkungan Hidup' => true,
                                    'Keamanan Komputer dan Lingkungan Hidup' => false,
                                    'Kesehatan Kerja dan Lingkungan Hardware' => false,
                                    'Kelistrikan Komputer dan Lingkungan Hidup' => false,
                                ],
                            ],
                            [
                                'pertanyaan' => 'Alat pelindung diri apa yang wajib digunakan saat merakit PC untuk mencegah kerusakan komponen akibat listrik statis?',
                                'pembahasan' => 'Gelang antistatis (wrist strap) menyalurkan muatan statis dari tubuh ke ground sehingga komponen elektronik tidak rusak.',
                                'opsi' => [
                                    'Gelang antistatis (wrist strap)' => true,
                                    'Sarung tangan karet' => false,
                                    'Kacamata las' => false,
                                    'Helm proyek' => false,
                                ],
                            ],
                            [
                                'pertanyaan' => 'Apa tujuan utama penerapan K3LH di laboratorium komputer?',
                                'pembahasan' => 'Tujuan utamanya adalah mencegah kecelakaan kerja dan menjaga keselamatan pengguna serta perangkat.',
                                'opsi' => [
                                    'Mencegah kecelakaan kerja dan menjaga keselamatan' => true,
                                    'Mempercepat proses perakitan komputer' => false,
                                    'Menghemat biaya listrik' => false,
                                    'Meningkatkan performa komputer' => false,
                                ],
                            ],
                            [
                                'pertanyaan' => 'Jenis APAR yang aman digunakan untuk memadamkan kebakaran akibat korsleting listrik adalah?',
                                'pembahasan' => 'APAR jenis CO2 atau dry chemical aman untuk kebakaran listrik karena tidak menghantarkan arus, berbeda dengan air.',
                                'opsi' => [
                                    'APAR jenis CO2 / dry chemical' => true,
                                    'Air biasa' => false,
                                    'Pasir basah' => false,
                                    'Busa sabun' => false,
                                ],
                            ],
                        ],
                    ],
                    [
                        'judul' => 'Perakitan PC',
                        'materi' => 'Perakitan PC meliputi pemasangan komponen seperti motherboard, processor, RAM, storage, dan power supply menjadi satu unit komputer yang berfungsi.',
                        'soal' => [
                            [
                                'pertanyaan' => 'Komponen yang berfungsi sebagai otak dari sebuah komputer adalah?',
                                'pembahasan' => 'Processor (CPU) memproses seluruh instruksi dan perhitungan pada komputer, sehingga disebut otak komputer.',
                                'opsi' => [
                                    'Processor (CPU)' => true,
                                    'RAM' => false,
                                    'Power Supply' => false,
                                    'Casing' => false,
                                ],
                            ],
                            [
                                'pertanyaan' => 'Konektor power utama pada motherboard ATX modern memiliki berapa pin?',
                                'pembahasan' => 'Konektor power utama ATX standar menggunakan 24 pin.',
                                'opsi' => [
                                    '24 pin' => true,
                                    '8 pin' => false,
                                    '4 pin' => false,
                                    '20 pin' => false,
                                ],
                            ],
                            [
                                'pertanyaan' => 'Komponen yang berfungsi menyimpan data secara permanen adalah?',
                                'pembahasan' => 'Hard disk atau SSD digunakan untuk menyimpan data secara permanen meskipun komputer dimatikan.',
                                'opsi' => [
                                    'Hard disk / SSD' => true,
                                    'RAM' => false,
                                    'Cache L1' => false,
                                    'VGA' => false,
                                ],
                            ],
                        ],
                    ],
                    [
                        'judul' => 'Instalasi Sistem Operasi (Windows & Linux)',
                        'materi' => 'Instalasi sistem operasi mencakup pembuatan partisi, pemilihan file system, dan konfigurasi awal untuk Windows maupun Linux.',
                        'soal' => [
                            [
                                'pertanyaan' => 'Pada sistem UEFI, partisi yang menyimpan bootloader disebut?',
                                'pembahasan' => 'EFI System Partition (ESP) menyimpan file bootloader yang dibutuhkan sistem UEFI untuk boot.',
                                'opsi' => [
                                    'EFI System Partition' => true,
                                    'Master Boot Record' => false,
                                    'Swap Partition' => false,
                                    'Recovery Partition' => false,
                                ],
                            ],
                            [
                                'pertanyaan' => 'Perintah dasar di terminal Linux untuk menampilkan isi direktori adalah?',
                                'pembahasan' => 'Perintah "ls" digunakan untuk menampilkan daftar file dan folder dalam direktori aktif.',
                                'opsi' => [
                                    'ls' => true,
                                    'cd' => false,
                                    'pwd' => false,
                                    'rm' => false,
                                ],
                            ],
                            [
                                'pertanyaan' => 'Format sistem file default yang digunakan Windows modern adalah?',
                                'pembahasan' => 'NTFS adalah file system default untuk instalasi Windows modern.',
                                'opsi' => [
                                    'NTFS' => true,
                                    'FAT16' => false,
                                    'ext4' => false,
                                    'HFS+' => false,
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            11 => [
                'deskripsi' => 'Pengalamatan IP, VLAN, dan routing untuk kelas 11.',
                'bab' => [
                    [
                        'judul' => 'IP Address dan Subnetting (VLSM/CIDR)',
                        'materi' => 'Subnetting membagi sebuah jaringan besar menjadi beberapa subnet yang lebih kecil menggunakan VLSM dan notasi CIDR.',
                        'soal' => [
                            [
                                'pertanyaan' => 'Subnet mask /26 menghasilkan berapa jumlah host yang dapat digunakan per subnet?',
                                'pembahasan' => '/26 menyisakan 6 bit host (2^6 - 2 = 62 host yang dapat digunakan).',
                                'opsi' => [
                                    '62 host' => true,
                                    '30 host' => false,
                                    '126 host' => false,
                                    '14 host' => false,
                                ],
                            ],
                            [
                                'pertanyaan' => 'Alamat IP 192.168.1.1 secara default termasuk dalam kelas IP address?',
                                'pembahasan' => 'Rentang 192.0.0.0 - 223.255.255.255 termasuk kelas C.',
                                'opsi' => [
                                    'Kelas C' => true,
                                    'Kelas A' => false,
                                    'Kelas B' => false,
                                    'Kelas D' => false,
                                ],
                            ],
                            [
                                'pertanyaan' => 'Notasi CIDR /24 setara dengan subnet mask?',
                                'pembahasan' => '/24 berarti 24 bit pertama adalah network, setara dengan 255.255.255.0.',
                                'opsi' => [
                                    '255.255.255.0' => true,
                                    '255.255.0.0' => false,
                                    '255.0.0.0' => false,
                                    '255.255.255.128' => false,
                                ],
                            ],
                            [
                                'pertanyaan' => 'VLSM (Variable Length Subnet Mask) digunakan untuk?',
                                'pembahasan' => 'VLSM memungkinkan pembagian subnet dengan ukuran berbeda-beda sesuai kebutuhan jumlah host di tiap segmen.',
                                'opsi' => [
                                    'Membagi subnet dengan ukuran berbeda sesuai kebutuhan' => true,
                                    'Mempercepat koneksi internet' => false,
                                    'Mengenkripsi alamat IP' => false,
                                    'Mengganti alamat MAC' => false,
                                ],
                            ],
                        ],
                    ],
                    [
                        'judul' => 'VLAN dan Trunking',
                        'materi' => 'VLAN memisahkan jaringan secara logis dalam satu switch fisik, sedangkan trunking menghubungkan beberapa VLAN antar switch.',
                        'soal' => [
                            [
                                'pertanyaan' => 'Protokol standar industri untuk trunking VLAN antar vendor adalah?',
                                'pembahasan' => 'IEEE 802.1Q adalah standar tagging VLAN yang didukung oleh berbagai vendor perangkat jaringan.',
                                'opsi' => [
                                    'IEEE 802.1Q' => true,
                                    'ISL (Inter-Switch Link)' => false,
                                    'HSRP' => false,
                                    'STP' => false,
                                ],
                            ],
                            [
                                'pertanyaan' => 'VLAN default yang aktif secara otomatis pada switch Cisco adalah?',
                                'pembahasan' => 'VLAN 1 adalah VLAN default bawaan switch Cisco.',
                                'opsi' => [
                                    'VLAN 1' => true,
                                    'VLAN 10' => false,
                                    'VLAN 99' => false,
                                    'VLAN 100' => false,
                                ],
                            ],
                            [
                                'pertanyaan' => 'Apa fungsi utama VLAN pada jaringan?',
                                'pembahasan' => 'VLAN memisahkan broadcast domain secara logis tanpa perlu perangkat fisik terpisah.',
                                'opsi' => [
                                    'Memisahkan broadcast domain secara logis' => true,
                                    'Mempercepat kabel fiber optik' => false,
                                    'Mengganti alamat IP otomatis' => false,
                                    'Menghubungkan ke internet' => false,
                                ],
                            ],
                        ],
                    ],
                    [
                        'judul' => 'Routing Statis, RIP, dan OSPF',
                        'materi' => 'Routing digunakan agar paket data dapat mencapai jaringan yang berbeda, baik secara statis maupun dinamis (RIP, OSPF).',
                        'soal' => [
                            [
                                'pertanyaan' => 'Routing protocol yang termasuk kategori distance vector adalah?',
                                'pembahasan' => 'RIP (Routing Information Protocol) adalah protokol distance vector yang menggunakan hop count.',
                                'opsi' => [
                                    'RIP' => true,
                                    'OSPF' => false,
                                    'BGP' => false,
                                    'IS-IS' => false,
                                ],
                            ],
                            [
                                'pertanyaan' => 'Routing protocol yang termasuk kategori link-state adalah?',
                                'pembahasan' => 'OSPF (Open Shortest Path First) adalah protokol link-state yang menggunakan algoritma Dijkstra.',
                                'opsi' => [
                                    'OSPF' => true,
                                    'RIP' => false,
                                    'RIPv1' => false,
                                    'EIGRP saja' => false,
                                ],
                            ],
                            [
                                'pertanyaan' => 'Berapa nilai administrative distance default untuk RIP?',
                                'pembahasan' => 'RIP memiliki administrative distance default sebesar 120.',
                                'opsi' => [
                                    '120' => true,
                                    '90' => false,
                                    '110' => false,
                                    '1' => false,
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            12 => [
                'deskripsi' => 'Administrasi server (DHCP, DNS, web, mail) serta keamanan jaringan untuk kelas 12.',
                'bab' => [
                    [
                        'judul' => 'DHCP dan DNS Server',
                        'materi' => 'DHCP memberikan alamat IP secara otomatis kepada klien, sedangkan DNS menerjemahkan nama domain menjadi alamat IP.',
                        'soal' => [
                            [
                                'pertanyaan' => 'DHCP server menggunakan port default?',
                                'pembahasan' => 'DHCP server mendengarkan permintaan pada UDP port 67.',
                                'opsi' => [
                                    'UDP 67' => true,
                                    'TCP 80' => false,
                                    'UDP 53' => false,
                                    'TCP 21' => false,
                                ],
                            ],
                            [
                                'pertanyaan' => 'Apa fungsi utama DNS?',
                                'pembahasan' => 'DNS menerjemahkan nama domain menjadi alamat IP agar mudah diakses manusia.',
                                'opsi' => [
                                    'Menerjemahkan nama domain menjadi alamat IP' => true,
                                    'Memberikan IP otomatis ke klien' => false,
                                    'Mengenkripsi lalu lintas web' => false,
                                    'Mengatur bandwidth jaringan' => false,
                                ],
                            ],
                            [
                                'pertanyaan' => 'Jenis record DNS yang memetakan nama domain ke alamat IPv4 adalah?',
                                'pembahasan' => 'A record memetakan nama domain ke alamat IPv4.',
                                'opsi' => [
                                    'A record' => true,
                                    'MX record' => false,
                                    'CNAME record' => false,
                                    'TXT record' => false,
                                ],
                            ],
                            [
                                'pertanyaan' => 'Proses 4 langkah pada DHCP dikenal dengan istilah?',
                                'pembahasan' => 'DORA singkatan dari Discover, Offer, Request, Acknowledge.',
                                'opsi' => [
                                    'DORA (Discover, Offer, Request, Acknowledge)' => true,
                                    'SYN-ACK' => false,
                                    'Three-way handshake' => false,
                                    'ARP request-reply' => false,
                                ],
                            ],
                        ],
                    ],
                    [
                        'judul' => 'Web Server, FTP, dan Mail Server',
                        'materi' => 'Web server melayani permintaan HTTP/HTTPS, FTP server melayani transfer file, dan mail server menangani pengiriman email.',
                        'soal' => [
                            [
                                'pertanyaan' => 'Port default yang digunakan protokol HTTP adalah?',
                                'pembahasan' => 'HTTP menggunakan port default 80.',
                                'opsi' => [
                                    '80' => true,
                                    '443' => false,
                                    '21' => false,
                                    '25' => false,
                                ],
                            ],
                            [
                                'pertanyaan' => 'FTP menggunakan port default untuk kontrol dan data secara berurutan?',
                                'pembahasan' => 'FTP menggunakan port 21 untuk kontrol dan port 20 untuk transfer data.',
                                'opsi' => [
                                    '21 dan 20' => true,
                                    '80 dan 443' => false,
                                    '25 dan 110' => false,
                                    '22 dan 23' => false,
                                ],
                            ],
                            [
                                'pertanyaan' => 'Protokol yang digunakan untuk mengirim email adalah?',
                                'pembahasan' => 'SMTP (Simple Mail Transfer Protocol) digunakan untuk mengirim email.',
                                'opsi' => [
                                    'SMTP' => true,
                                    'POP3' => false,
                                    'IMAP' => false,
                                    'FTP' => false,
                                ],
                            ],
                        ],
                    ],
                    [
                        'judul' => 'Firewall, NAT, dan VPN',
                        'materi' => 'Firewall menyaring lalu lintas jaringan, NAT menerjemahkan alamat IP privat-publik, dan VPN membuat koneksi aman melalui jaringan publik.',
                        'soal' => [
                            [
                                'pertanyaan' => 'Apa fungsi utama firewall dalam jaringan?',
                                'pembahasan' => 'Firewall berfungsi menyaring atau memfilter lalu lintas jaringan berdasarkan aturan keamanan.',
                                'opsi' => [
                                    'Menyaring / memfilter lalu lintas jaringan' => true,
                                    'Mempercepat koneksi internet' => false,
                                    'Memberikan alamat IP otomatis' => false,
                                    'Menerjemahkan nama domain' => false,
                                ],
                            ],
                            [
                                'pertanyaan' => 'Teknik NAT yang memetakan banyak IP privat ke satu IP publik disebut?',
                                'pembahasan' => 'PAT (Port Address Translation), juga dikenal sebagai NAT Overload, memetakan banyak IP privat ke satu IP publik menggunakan nomor port.',
                                'opsi' => [
                                    'PAT (NAT Overload)' => true,
                                    'Static NAT' => false,
                                    'Dynamic NAT satu-ke-satu' => false,
                                    'NAT64' => false,
                                ],
                            ],
                            [
                                'pertanyaan' => 'Protokol yang umum digunakan untuk membangun tunnel VPN yang aman adalah?',
                                'pembahasan' => 'IPSec dan OpenVPN adalah protokol umum untuk membangun tunnel VPN yang aman.',
                                'opsi' => [
                                    'IPSec / OpenVPN' => true,
                                    'FTP' => false,
                                    'Telnet' => false,
                                    'SNMP' => false,
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}
