<?php

namespace Database\Seeders;

use App\Models\Bab;
use App\Models\ReferensiBelajar;
use Illuminate\Database\Seeder;

class ReferensiBelajarSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->referensi() as $judulBab => $data) {
            $bab = Bab::where('judul', $judulBab)->first();

            if (! $bab) {
                continue;
            }

            ReferensiBelajar::updateOrCreate(['bab_id' => $bab->id], $data);
        }
    }

    private function referensi(): array
    {
        return [
            'K3LH (Keselamatan dan Kesehatan Kerja serta Lingkungan Hidup)' => [
                'ringkasan' => $this->k3lh(),
                'video_judul' => 'Dasar-Dasar TJKT: Kesehatan, Keselamatan Kerja, dan Lingkungan Hidup (K3LH)',
                'video_url' => 'https://www.youtube.com/watch?v=dY5ULxrOXXg',
                'kasus_soal' => 'Seorang siswa akan merakit PC di laboratorium tanpa melepas perhiasan logam dan tanpa memakai gelang antistatis. Setelah proses perakitan selesai dan komputer dinyalakan, tidak ada tanda-tanda hidup sama sekali. Identifikasi minimal 2 pelanggaran prinsip K3LH pada skenario tersebut, jelaskan risiko yang dapat terjadi, dan berikan langkah pencegahannya.',
            ],
            'Perakitan PC' => [
                'ringkasan' => $this->perakitanPc(),
                'video_judul' => 'Tutorial Merakit PC - Panduan Lengkap untuk Pemula',
                'video_url' => 'https://www.youtube.com/watch?v=7Jh8urGXpro',
                'kasus_soal' => 'Sebuah PC yang baru selesai dirakit tidak mau menyala sama sekali: tidak ada lampu indikator dan kipas tidak berputar, padahal kabel power sudah tersambung dengan baik ke stop kontak yang berfungsi. Susun urutan langkah troubleshooting yang harus dilakukan seorang teknisi TKJ untuk menemukan penyebabnya, mulai dari pemeriksaan paling sederhana.',
            ],
            'Instalasi Sistem Operasi (Windows & Linux)' => [
                'ringkasan' => $this->instalasiOs(),
                'video_judul' => 'Cara Mudah Membuat Dual Boot Windows dan Linux (Tutorial Bahasa Indonesia)',
                'video_url' => 'https://www.youtube.com/watch?v=9K6XAbor_8Q',
                'kasus_soal' => 'Sebuah laptop dengan 1 unit harddisk berkapasitas 500GB yang sudah berisi data penting akan diinstal dual boot Windows 10 dan Ubuntu Linux tanpa kehilangan data yang ada. Jelaskan skema partisi (minimal 4 partisi) beserta jenis file system yang tepat untuk masing-masing partisi tersebut.',
            ],
            'IP Address dan Subnetting (VLSM/CIDR)' => [
                'ringkasan' => $this->subnetting(),
                'video_judul' => 'Cara Menghitung Subnetting dengan VLSM (Dengan Contoh Kasus)',
                'video_url' => 'https://www.youtube.com/watch?v=eDswSRR2eNQ',
                'kasus_soal' => 'Sebuah kantor mendapat alokasi jaringan 192.168.10.0/24 yang harus dibagi untuk 4 departemen dengan kebutuhan host masing-masing: Marketing (50 host), Keuangan (25 host), IT (10 host), dan Gudang (5 host). Tentukan pembagian subnet menggunakan metode VLSM agar alokasi alamat IP paling efisien, lalu tuliskan network address dan range host untuk setiap departemen.',
            ],
            'VLAN dan Trunking' => [
                'ringkasan' => $this->vlan(),
                'video_judul' => 'Konfigurasi VLAN Trunking - Cisco Packet Tracer',
                'video_url' => 'https://www.youtube.com/watch?v=kmnHZNjfGWE',
                'kasus_soal' => 'Sebuah gedung sekolah memiliki 3 lantai dengan switch berbeda di setiap lantai yang perlu dihubungkan satu sama lain: Lantai 1 untuk Guru (VLAN 10), Lantai 2 untuk Siswa (VLAN 20), dan Lantai 3 untuk Admin (VLAN 30). Rancang skema VLAN untuk topologi ini dan jelaskan mengapa port yang menghubungkan antar switch harus dikonfigurasi sebagai trunk, bukan access port.',
            ],
            'Routing Statis, RIP, dan OSPF' => [
                'ringkasan' => $this->routing(),
                'video_judul' => 'Tutorial Routing RIP dan OSPF dengan Cisco Packet Tracer',
                'video_url' => 'https://www.youtube.com/watch?v=7GT3vRYBZhQ',
                'kasus_soal' => 'Tiga kantor cabang (Jakarta, Bandung, dan Surabaya) terhubung melalui 3 router membentuk topologi segitiga (mesh), dan perusahaan berencana sering menambah cabang baru di kota lain. Rekomendasikan jenis routing yang paling cocok digunakan (statis, RIP, atau OSPF) untuk kondisi ini beserta alasannya.',
            ],
            'DHCP dan DNS Server' => [
                'ringkasan' => $this->dhcpDns(),
                'video_judul' => 'Cara Install DHCP Server di Debian 11 untuk Pemula (Step by Step)',
                'video_url' => 'https://www.youtube.com/watch?v=URgbvdxSXXg',
                'kasus_soal' => 'Sebuah warnet dengan 30 unit komputer klien mengeluhkan proses pengaturan IP address secara manual memakan waktu lama dan sering terjadi IP conflict antar komputer. Jelaskan solusi menggunakan DHCP server untuk kasus ini, beserta 4 tahapan proses DORA yang terjadi ketika sebuah klien meminta alamat IP ke server.',
            ],
            'Web Server, FTP, dan Mail Server' => [
                'ringkasan' => $this->webFtpMail(),
                'video_judul' => 'Konfigurasi SSH, DNS Server, Web Server, FTP Server, dan Mail Server di Debian',
                'video_url' => 'https://www.youtube.com/watch?v=yv8iq2nBl40',
                'kasus_soal' => 'Sebuah sekolah ingin membangun server internal yang dapat diakses oleh siswa untuk tiga keperluan: (1) membuka website informasi sekolah, (2) mengunggah dan mengunduh berkas tugas, dan (3) mengirim email antar guru. Tentukan jenis server yang dibutuhkan untuk masing-masing keperluan tersebut beserta port default layanannya.',
            ],
            'Firewall, NAT, dan VPN' => [
                'ringkasan' => $this->firewallNatVpn(),
                'video_judul' => 'Fungsi NAT di MikroTik dan Cara Setting Lengkap (Masquerade)',
                'video_url' => 'https://www.youtube.com/watch?v=qCAVpNi2APs',
                'kasus_soal' => 'Sebuah kantor memiliki 20 unit komputer dengan alamat IP privat (192.168.1.0/24) namun hanya mendapat 1 alamat IP publik dari ISP. Selain itu, karyawan yang bekerja dari rumah perlu mengakses server internal kantor dengan aman melalui internet. Jelaskan teknik NAT yang digunakan agar semua komputer dapat mengakses internet, serta solusi VPN yang tepat untuk kebutuhan akses remote yang aman tersebut.',
            ],
        ];
    }

    private function k3lh(): string
    {
        return <<<'HTML'
<h3>Pengertian K3LH</h3>
<p><strong>K3LH</strong> adalah singkatan dari <em>Keselamatan dan Kesehatan Kerja serta Lingkungan Hidup</em>, yaitu upaya terencana untuk menjamin keselamatan dan kesehatan pekerja sekaligus menjaga kelestarian lingkungan di tempat kerja. Di bidang TKJ, K3LH diterapkan di laboratorium komputer, ruang server, dan saat bekerja di lapangan (instalasi jaringan).</p>

<h3>Tujuan Penerapan K3LH</h3>
<ul>
<li>Mencegah kecelakaan kerja dan penyakit akibat kerja.</li>
<li>Melindungi peralatan agar tidak rusak akibat kesalahan penanganan.</li>
<li>Menjamin proses kerja berjalan aman, efisien, dan produktif.</li>
<li>Mengelola limbah elektronik agar tidak mencemari lingkungan.</li>
</ul>

<h3>Potensi Bahaya di Laboratorium TKJ</h3>
<ul>
<li><strong>Bahaya listrik</strong> — sengatan listrik dan korsleting dari kabel terkelupas, stop kontak kelebihan beban, atau power supply rusak.</li>
<li><strong>Listrik statis (ESD)</strong> — muatan statis dari tubuh dapat merusak komponen elektronik seperti prosesor, RAM, dan motherboard meskipun tidak terlihat secara fisik.</li>
<li><strong>Bahaya mekanis</strong> — tergores casing tajam, tertimpa monitor/CPU, atau terjepit saat memasang komponen.</li>
<li><strong>Bahaya ergonomis</strong> — posisi duduk salah dan menatap layar terlalu lama menyebabkan nyeri punggung, <em>carpal tunnel syndrome</em>, dan kelelahan mata.</li>
<li><strong>Limbah B3</strong> — baterai CMOS, cartridge tinta, dan komponen bekas mengandung bahan berbahaya.</li>
</ul>

<h3>Alat Pelindung Diri (APD)</h3>
<ul>
<li><strong>Gelang antistatis (wrist strap)</strong> — wajib saat merakit PC; menyalurkan muatan statis tubuh ke ground.</li>
<li><strong>Sarung tangan</strong> — melindungi dari sisi casing yang tajam.</li>
<li><strong>Sepatu berinsulasi (safety shoes)</strong> — mencegah sengatan listrik saat bekerja di ruang server.</li>
<li><strong>Masker</strong> — saat membersihkan debu di dalam casing atau ruang server.</li>
</ul>

<h3>Prosedur Kerja Aman Merakit PC</h3>
<ol>
<li>Pastikan kabel power <strong>dicabut</strong> dari sumber listrik sebelum membuka casing.</li>
<li>Lepas perhiasan logam (cincin, gelang, jam tangan) yang dapat menimbulkan hubungan singkat.</li>
<li>Pakai gelang antistatis, atau sentuh bagian logam casing lebih dulu untuk menetralkan muatan statis.</li>
<li>Pegang komponen pada bagian tepi — hindari menyentuh jalur PCB dan pin konektor.</li>
<li>Simpan komponen pada alas antistatis, bukan di atas karpet atau kain sintetis.</li>
<li>Gunakan alat sesuai fungsinya dan rapikan kembali meja kerja setelah selesai.</li>
</ol>

<h3>Klasifikasi Kebakaran dan APAR</h3>
<table>
<thead><tr><th>Kelas</th><th>Sumber Api</th><th>APAR yang Tepat</th></tr></thead>
<tbody>
<tr><td>A</td><td>Kayu, kertas, kain</td><td>Air, dry chemical</td></tr>
<tr><td>B</td><td>Cairan mudah terbakar (bensin, oli)</td><td>Busa (foam), dry chemical, CO<sub>2</sub></td></tr>
<tr><td>C</td><td><strong>Listrik / peralatan elektronik</strong></td><td><strong>CO<sub>2</sub> atau dry chemical</strong> (jangan gunakan air)</td></tr>
<tr><td>D</td><td>Logam mudah terbakar</td><td>Bubuk khusus (dry powder)</td></tr>
</tbody>
</table>
<p>Di laboratorium komputer, kebakaran umumnya termasuk <strong>kelas C</strong> sehingga APAR CO<sub>2</sub> menjadi pilihan utama karena tidak menghantarkan listrik dan tidak meninggalkan residu yang merusak perangkat.</p>

<h3>Pengelolaan Limbah Elektronik (E-Waste)</h3>
<p>Komponen bekas seperti baterai, monitor CRT, dan PCB tidak boleh dibuang bersama sampah rumah tangga karena mengandung timbal, merkuri, dan kadmium. Prinsip pengelolaannya menggunakan <strong>3R</strong>: <em>Reduce</em> (kurangi pembelian berlebih), <em>Reuse</em> (gunakan kembali komponen yang masih layak), dan <em>Recycle</em> (serahkan ke pengepul resmi e-waste).</p>
HTML;
    }

    private function perakitanPc(): string
    {
        return <<<'HTML'
<h3>Komponen Utama PC dan Fungsinya</h3>
<table>
<thead><tr><th>Komponen</th><th>Fungsi</th></tr></thead>
<tbody>
<tr><td><strong>Processor (CPU)</strong></td><td>Otak komputer; mengeksekusi seluruh instruksi dan perhitungan.</td></tr>
<tr><td><strong>Motherboard</strong></td><td>Papan induk yang menghubungkan seluruh komponen.</td></tr>
<tr><td><strong>RAM</strong></td><td>Memori sementara tempat data diproses; hilang saat komputer mati.</td></tr>
<tr><td><strong>Storage (HDD/SSD)</strong></td><td>Penyimpanan permanen sistem operasi dan data.</td></tr>
<tr><td><strong>Power Supply (PSU)</strong></td><td>Mengubah listrik AC menjadi DC dan mendistribusikan daya.</td></tr>
<tr><td><strong>VGA Card</strong></td><td>Memproses tampilan grafis ke monitor.</td></tr>
<tr><td><strong>Heatsink &amp; Fan</strong></td><td>Membuang panas prosesor agar suhu tetap stabil.</td></tr>
<tr><td><strong>Casing</strong></td><td>Rumah seluruh komponen sekaligus pelindung fisik.</td></tr>
</tbody>
</table>

<h3>Alat dan Bahan</h3>
<ul>
<li>Obeng plus (+) magnetik dan obeng minus (-)</li>
<li>Gelang antistatis</li>
<li>Thermal paste (pasta pendingin)</li>
<li>Kabel ties untuk manajemen kabel</li>
<li>Kuas/blower untuk membersihkan debu</li>
</ul>

<h3>Langkah-Langkah Perakitan</h3>
<ol>
<li><strong>Pasang processor</strong> — buka tuas socket, sejajarkan tanda segitiga pada CPU dengan tanda pada socket, letakkan tanpa ditekan, lalu kunci tuasnya.</li>
<li><strong>Oleskan thermal paste</strong> secukupnya (sebesar biji jagung) di tengah permukaan processor.</li>
<li><strong>Pasang heatsink dan fan</strong>, kencangkan menyilang agar tekanan merata, lalu sambungkan kabel fan ke konektor <code>CPU_FAN</code>.</li>
<li><strong>Pasang RAM</strong> — buka pengait slot, sejajarkan notch (celah) pada keping RAM dengan slot, tekan sampai pengait mengunci sendiri (terdengar bunyi klik). Untuk dual channel, gunakan slot dengan warna yang sama.</li>
<li><strong>Pasang motherboard ke casing</strong> di atas <em>standoff</em> (baut penyangga) agar tidak bersentuhan langsung dengan pelat casing.</li>
<li><strong>Pasang power supply</strong>, lalu sambungkan konektor <strong>24-pin ATX</strong> ke motherboard dan <strong>4/8-pin EPS</strong> untuk daya processor.</li>
<li><strong>Pasang storage</strong> (HDD/SSD) pada bay, sambungkan kabel SATA data ke motherboard dan SATA power dari PSU.</li>
<li><strong>Pasang VGA card</strong> pada slot PCIe paling atas bila tidak menggunakan grafis onboard.</li>
<li><strong>Sambungkan kabel front panel</strong> — Power SW, Reset SW, HDD LED, Power LED, USB, dan Audio sesuai tanda pada motherboard.</li>
<li><strong>Rapikan kabel</strong>, periksa ulang seluruh sambungan, tutup casing, lalu nyalakan komputer.</li>
</ol>

<h3>POST dan Kode Beep</h3>
<p><strong>POST</strong> (<em>Power On Self Test</em>) adalah pengujian otomatis perangkat keras saat komputer dinyalakan. Jika ditemukan masalah, BIOS memberi tanda berupa bunyi beep:</p>
<ul>
<li><strong>1 beep pendek</strong> — POST berhasil, sistem normal.</li>
<li><strong>Beep panjang berulang</strong> — umumnya masalah pada RAM (tidak terpasang sempurna atau rusak).</li>
<li><strong>1 beep panjang + 2/3 beep pendek</strong> — umumnya masalah pada VGA card.</li>
<li><strong>Tidak ada beep &amp; tidak menyala</strong> — periksa PSU, kabel power, dan konektor front panel.</li>
</ul>
<p class="catatan"><em>Catatan: pola beep dapat berbeda tergantung merek BIOS (AMI, Award, Phoenix). Selalu cek manual motherboard.</em></p>

<h3>Troubleshooting Dasar</h3>
<table>
<thead><tr><th>Gejala</th><th>Kemungkinan Penyebab</th><th>Solusi</th></tr></thead>
<tbody>
<tr><td>Tidak menyala sama sekali</td><td>Kabel power, PSU, atau front panel salah pasang</td><td>Cek stop kontak, saklar PSU, dan posisi kabel Power SW</td></tr>
<tr><td>Menyala tapi tidak tampil di monitor</td><td>RAM/VGA tidak terpasang sempurna</td><td>Cabut dan pasang ulang RAM serta VGA, bersihkan pin</td></tr>
<tr><td>Restart sendiri berulang</td><td>Overheat atau PSU kurang daya</td><td>Cek pasta dan kipas processor, hitung ulang kebutuhan watt</td></tr>
<tr><td>Storage tidak terbaca</td><td>Kabel SATA longgar</td><td>Ganti/kencangkan kabel SATA, cek deteksi di BIOS</td></tr>
</tbody>
</table>
HTML;
    }

    private function instalasiOs(): string
    {
        return <<<'HTML'
<h3>Persiapan Sebelum Instalasi</h3>
<ul>
<li>Siapkan file ISO sistem operasi (Windows/Linux) dan flashdisk minimal 8GB.</li>
<li>Buat media bootable menggunakan Rufus, Ventoy, atau BalenaEtcher.</li>
<li><strong>Backup data penting</strong> terlebih dahulu — proses partisi berisiko menghapus data.</li>
<li>Catat spesifikasi perangkat dan siapkan driver (terutama driver chipset dan LAN).</li>
</ul>

<h3>BIOS vs UEFI</h3>
<table>
<thead><tr><th>Aspek</th><th>BIOS (Legacy)</th><th>UEFI</th></tr></thead>
<tbody>
<tr><td>Skema partisi</td><td>MBR</td><td>GPT</td></tr>
<tr><td>Kapasitas disk maksimal</td><td>± 2 TB</td><td>Di atas 2 TB (hingga 9,4 ZB)</td></tr>
<tr><td>Jumlah partisi primary</td><td>Maksimal 4</td><td>Hingga 128 partisi</td></tr>
<tr><td>Fitur keamanan</td><td>Terbatas</td><td>Mendukung Secure Boot</td></tr>
<tr><td>Antarmuka</td><td>Teks saja</td><td>Grafis, mendukung mouse</td></tr>
</tbody>
</table>

<h3>Partisi dan File System</h3>
<table>
<thead><tr><th>Partisi</th><th>File System</th><th>Keterangan</th></tr></thead>
<tbody>
<tr><td>EFI System Partition (ESP)</td><td>FAT32</td><td>± 512 MB, menyimpan bootloader pada sistem UEFI</td></tr>
<tr><td>Partisi Windows (C:)</td><td>NTFS</td><td>Sistem operasi Windows dan program</td></tr>
<tr><td>Partisi Data (D:)</td><td>NTFS</td><td>Data pengguna, aman saat install ulang Windows</td></tr>
<tr><td>Root Linux ( / )</td><td>ext4</td><td>Sistem operasi Linux, minimal 20-25 GB</td></tr>
<tr><td>Home Linux ( /home )</td><td>ext4</td><td>Data pengguna Linux (opsional tapi disarankan)</td></tr>
<tr><td>Swap</td><td>linux-swap</td><td>Memori cadangan, ± 1-2x kapasitas RAM</td></tr>
</tbody>
</table>

<h3>Langkah Instalasi Windows</h3>
<ol>
<li>Masuk BIOS/UEFI (tekan Del/F2 saat booting), atur <em>boot priority</em> ke flashdisk.</li>
<li>Pilih bahasa, waktu, dan layout keyboard, lalu klik <em>Install Now</em>.</li>
<li>Pilih <strong>Custom: Install Windows only (advanced)</strong> untuk mengatur partisi manual.</li>
<li>Buat/pilih partisi tujuan instalasi, lalu klik Next dan tunggu proses penyalinan file.</li>
<li>Komputer restart beberapa kali — cabut flashdisk agar tidak kembali ke installer.</li>
<li>Lakukan konfigurasi awal: region, akun pengguna, dan pengaturan privasi.</li>
</ol>

<h3>Langkah Instalasi Linux (Ubuntu/Debian)</h3>
<ol>
<li>Boot dari media instalasi, pilih <em>Try or Install</em>.</li>
<li>Pilih bahasa, keyboard, dan jenis instalasi (Normal/Minimal).</li>
<li>Pada tahap partisi pilih <strong>Something else</strong> untuk membuat partisi manual: <code>/</code> (ext4), <code>/home</code> (ext4), dan <code>swap</code>.</li>
<li>Tentukan lokasi pemasangan bootloader <strong>GRUB</strong> (biasanya di disk utama, contoh <code>/dev/sda</code>).</li>
<li>Isi data pengguna dan password, tunggu instalasi selesai, lalu restart.</li>
</ol>

<h3>Konsep Dual Boot</h3>
<p>Dual boot memungkinkan dua sistem operasi terpasang pada satu komputer. Aturan pentingnya:</p>
<ul>
<li><strong>Install Windows terlebih dahulu</strong>, baru Linux. Bootloader Windows akan menimpa GRUB jika urutannya dibalik.</li>
<li>Sisakan <em>unallocated space</em> untuk Linux saat menginstal Windows.</li>
<li>GRUB akan otomatis mendeteksi Windows dan menampilkannya di menu boot.</li>
<li>Nonaktifkan <em>Fast Startup</em> di Windows agar partisi tidak terkunci saat diakses dari Linux.</li>
</ul>

<h3>Tahap Pasca Instalasi</h3>
<ul>
<li>Install driver (chipset, VGA, LAN/WiFi, audio) dan lakukan update sistem.</li>
<li>Pasang aplikasi dasar dan antivirus (untuk Windows).</li>
<li>Aktivasi sistem operasi sesuai lisensi yang sah.</li>
<li>Buat <em>restore point</em> atau image cadangan sistem.</li>
</ul>
HTML;
    }

    private function subnetting(): string
    {
        return <<<'HTML'
<h3>Struktur Alamat IPv4</h3>
<p>Alamat IPv4 terdiri dari <strong>32 bit</strong> yang dibagi menjadi 4 oktet (masing-masing 8 bit), ditulis dalam notasi desimal bertitik, contoh <code>192.168.1.10</code>. Setiap alamat memiliki dua bagian: <strong>Network ID</strong> (menunjukkan jaringan) dan <strong>Host ID</strong> (menunjukkan perangkat dalam jaringan tersebut). Pembatas keduanya ditentukan oleh <strong>subnet mask</strong>.</p>

<h3>Kelas IP Address</h3>
<table>
<thead><tr><th>Kelas</th><th>Rentang Oktet Pertama</th><th>Subnet Mask Default</th><th>Penggunaan</th></tr></thead>
<tbody>
<tr><td>A</td><td>1 - 126</td><td>255.0.0.0 (/8)</td><td>Jaringan sangat besar</td></tr>
<tr><td>B</td><td>128 - 191</td><td>255.255.0.0 (/16)</td><td>Jaringan menengah</td></tr>
<tr><td>C</td><td>192 - 223</td><td>255.255.255.0 (/24)</td><td>Jaringan kecil (LAN)</td></tr>
<tr><td>D</td><td>224 - 239</td><td>-</td><td>Multicast</td></tr>
<tr><td>E</td><td>240 - 255</td><td>-</td><td>Eksperimen/riset</td></tr>
</tbody>
</table>
<p class="catatan"><em>127.x.x.x tidak dipakai untuk host karena dicadangkan sebagai loopback.</em></p>

<h3>IP Privat dan IP Publik</h3>
<p>Alamat privat (RFC 1918) hanya dipakai di jaringan lokal dan tidak dirutekan di internet:</p>
<ul>
<li><code>10.0.0.0 - 10.255.255.255</code> (10.0.0.0/8)</li>
<li><code>172.16.0.0 - 172.31.255.255</code> (172.16.0.0/12)</li>
<li><code>192.168.0.0 - 192.168.255.255</code> (192.168.0.0/16)</li>
</ul>
<p>Agar dapat mengakses internet, IP privat diterjemahkan menjadi IP publik menggunakan <strong>NAT</strong>.</p>

<h3>CIDR dan Subnet Mask</h3>
<p><strong>CIDR</strong> (<em>Classless Inter-Domain Routing</em>) menuliskan subnet mask sebagai jumlah bit network, misalnya <code>/24</code> setara <code>255.255.255.0</code>. Rumus dasar subnetting:</p>
<ul>
<li>Jumlah subnet = 2<sup>x</sup>, dengan <em>x</em> = jumlah bit yang dipinjam dari host.</li>
<li>Jumlah host per subnet = 2<sup>y</sup> - 2, dengan <em>y</em> = jumlah bit host tersisa (32 - prefix).</li>
<li>Blok subnet (<em>block size</em>) = 256 - nilai oktet subnet mask yang diubah.</li>
</ul>
<p>Pengurangan 2 pada jumlah host karena satu alamat dipakai sebagai <strong>network address</strong> dan satu lagi sebagai <strong>broadcast address</strong>.</p>

<h3>Tabel Prefix yang Sering Dipakai</h3>
<table>
<thead><tr><th>Prefix</th><th>Subnet Mask</th><th>Block Size</th><th>Host Usable</th></tr></thead>
<tbody>
<tr><td>/24</td><td>255.255.255.0</td><td>256</td><td>254</td></tr>
<tr><td>/25</td><td>255.255.255.128</td><td>128</td><td>126</td></tr>
<tr><td>/26</td><td>255.255.255.192</td><td>64</td><td>62</td></tr>
<tr><td>/27</td><td>255.255.255.224</td><td>32</td><td>30</td></tr>
<tr><td>/28</td><td>255.255.255.240</td><td>16</td><td>14</td></tr>
<tr><td>/29</td><td>255.255.255.248</td><td>8</td><td>6</td></tr>
<tr><td>/30</td><td>255.255.255.252</td><td>4</td><td>2</td></tr>
</tbody>
</table>

<h3>Contoh Perhitungan Subnetting</h3>
<p>Diketahui <strong>192.168.1.0/26</strong>:</p>
<ul>
<li>Subnet mask = 255.255.255.192, block size = 256 - 192 = <strong>64</strong>.</li>
<li>Jumlah host = 2<sup>6</sup> - 2 = <strong>62 host</strong> per subnet.</li>
<li>Subnet yang terbentuk: 192.168.1.0, 192.168.1.64, 192.168.1.128, 192.168.1.192.</li>
<li>Untuk subnet pertama: network <code>192.168.1.0</code>, range host <code>192.168.1.1 - 192.168.1.62</code>, broadcast <code>192.168.1.63</code>.</li>
</ul>

<h3>VLSM (Variable Length Subnet Mask)</h3>
<p>VLSM membagi jaringan menjadi subnet dengan ukuran <strong>berbeda-beda</strong> sesuai kebutuhan, sehingga alamat IP tidak terbuang. Langkahnya:</p>
<ol>
<li>Urutkan kebutuhan host dari yang <strong>terbesar ke terkecil</strong>.</li>
<li>Tentukan prefix terkecil yang mencukupi tiap kebutuhan (ingat rumus 2<sup>y</sup> - 2).</li>
<li>Alokasikan mulai dari blok pertama secara berurutan tanpa saling tumpang tindih.</li>
<li>Catat network address, range host, dan broadcast tiap subnet.</li>
</ol>
<p><strong>Contoh:</strong> jaringan <code>192.168.1.0/24</code> untuk kebutuhan 100, 50, 25, dan 2 host:</p>
<table>
<thead><tr><th>Kebutuhan</th><th>Prefix</th><th>Network</th><th>Range Host</th><th>Broadcast</th></tr></thead>
<tbody>
<tr><td>100 host</td><td>/25 (126 host)</td><td>192.168.1.0</td><td>.1 - .126</td><td>192.168.1.127</td></tr>
<tr><td>50 host</td><td>/26 (62 host)</td><td>192.168.1.128</td><td>.129 - .190</td><td>192.168.1.191</td></tr>
<tr><td>25 host</td><td>/27 (30 host)</td><td>192.168.1.192</td><td>.193 - .222</td><td>192.168.1.223</td></tr>
<tr><td>2 host (link WAN)</td><td>/30 (2 host)</td><td>192.168.1.224</td><td>.225 - .226</td><td>192.168.1.227</td></tr>
</tbody>
</table>
<p class="catatan"><em>Gunakan menu <strong>Alat Bantu &rarr; Subnet Calculator</strong> pada aplikasi ini untuk memverifikasi hasil hitunganmu.</em></p>
HTML;
    }

    private function vlan(): string
    {
        return <<<'HTML'
<h3>Konsep VLAN</h3>
<p><strong>VLAN</strong> (<em>Virtual Local Area Network</em>) adalah teknik memisahkan satu switch fisik menjadi beberapa jaringan logis yang terisolasi. Perangkat pada VLAN berbeda tidak dapat saling berkomunikasi secara langsung walaupun terhubung ke switch yang sama, kecuali melalui router atau Layer 3 switch.</p>

<h3>Manfaat VLAN</h3>
<ul>
<li><strong>Memperkecil broadcast domain</strong> — traffic broadcast hanya beredar di dalam VLAN-nya sendiri sehingga jaringan lebih efisien.</li>
<li><strong>Keamanan</strong> — data antar bagian (misalnya Guru dan Siswa) terpisah secara logis.</li>
<li><strong>Fleksibilitas</strong> — pengelompokan berdasarkan fungsi/departemen, bukan lokasi fisik.</li>
<li><strong>Hemat biaya</strong> — tidak perlu membeli switch terpisah untuk tiap kelompok jaringan.</li>
</ul>

<h3>Jenis Port pada Switch</h3>
<table>
<thead><tr><th>Jenis Port</th><th>Fungsi</th><th>Jumlah VLAN</th></tr></thead>
<tbody>
<tr><td><strong>Access Port</strong></td><td>Menghubungkan perangkat akhir (PC, printer, AP)</td><td>Hanya 1 VLAN</td></tr>
<tr><td><strong>Trunk Port</strong></td><td>Menghubungkan switch ke switch atau switch ke router</td><td>Banyak VLAN sekaligus</td></tr>
</tbody>
</table>

<h3>Trunking dan Tagging 802.1Q</h3>
<p><strong>Trunking</strong> memungkinkan traffic dari banyak VLAN melewati satu kabel. Agar switch tujuan tahu sebuah frame milik VLAN mana, frame diberi label menggunakan standar <strong>IEEE 802.1Q</strong> — yaitu penyisipan <em>tag</em> 4 byte berisi VLAN ID (1-4094) pada header frame Ethernet.</p>
<ul>
<li><strong>IEEE 802.1Q</strong> — standar terbuka, didukung semua vendor (Cisco, MikroTik, HP, dsb).</li>
<li><strong>ISL</strong> — protokol trunking lama milik Cisco, kini sudah ditinggalkan.</li>
<li><strong>Native VLAN</strong> — VLAN yang framenya dikirim <em>tanpa tag</em> melalui trunk (default VLAN 1). Native VLAN di kedua ujung trunk harus sama agar tidak terjadi <em>VLAN mismatch</em>.</li>
</ul>

<h3>Konfigurasi VLAN di Cisco</h3>
<p>Membuat VLAN dan memberi nama:</p>
<pre><code>Switch&gt; enable
Switch# configure terminal
Switch(config)# vlan 10
Switch(config-vlan)# name Guru
Switch(config-vlan)# exit
Switch(config)# vlan 20
Switch(config-vlan)# name Siswa
Switch(config-vlan)# exit</code></pre>

<p>Mengatur access port (port ke PC):</p>
<pre><code>Switch(config)# interface fastEthernet 0/1
Switch(config-if)# switchport mode access
Switch(config-if)# switchport access vlan 10
Switch(config-if)# exit</code></pre>

<p>Mengatur trunk port (port antar switch):</p>
<pre><code>Switch(config)# interface gigabitEthernet 0/1
Switch(config-if)# switchport mode trunk
Switch(config-if)# switchport trunk allowed vlan 10,20,30
Switch(config-if)# exit</code></pre>

<p>Verifikasi konfigurasi:</p>
<pre><code>Switch# show vlan brief
Switch# show interfaces trunk</code></pre>

<h3>Inter-VLAN Routing (Router on a Stick)</h3>
<p>Agar VLAN berbeda dapat saling berkomunikasi, dibutuhkan perangkat Layer 3. Metode paling umum di SMK adalah <em>router on a stick</em>, yaitu satu interface router dibagi menjadi beberapa <strong>sub-interface</strong>:</p>
<pre><code>Router(config)# interface gigabitEthernet 0/0.10
Router(config-subif)# encapsulation dot1Q 10
Router(config-subif)# ip address 192.168.10.1 255.255.255.0
Router(config-subif)# exit
Router(config)# interface gigabitEthernet 0/0.20
Router(config-subif)# encapsulation dot1Q 20
Router(config-subif)# ip address 192.168.20.1 255.255.255.0</code></pre>
<p>Alamat IP pada tiap sub-interface berfungsi sebagai <strong>default gateway</strong> bagi perangkat di VLAN tersebut.</p>
HTML;
    }

    private function routing(): string
    {
        return <<<'HTML'
<h3>Konsep Routing</h3>
<p><strong>Routing</strong> adalah proses menentukan jalur terbaik untuk mengirim paket data dari satu jaringan ke jaringan lain. Perangkat yang melakukannya adalah <strong>router</strong>, yang bekerja di <em>Layer 3 (Network)</em> pada model OSI dengan berpedoman pada <strong>routing table</strong>.</p>
<p>Isi routing table antara lain: jaringan tujuan, subnet mask, gateway (next hop), interface keluar, serta nilai <em>metric</em> dan <em>administrative distance</em>.</p>

<h3>Routing Statis</h3>
<p>Rute dimasukkan manual oleh administrator. Cocok untuk jaringan kecil dan stabil.</p>
<pre><code>Router(config)# ip route 192.168.2.0 255.255.255.0 10.10.10.2</code></pre>
<p>Format: <code>ip route [network tujuan] [subnet mask] [next hop]</code></p>
<p><strong>Default route</strong> dipakai untuk mengarahkan semua traffic yang tujuannya tidak ada di routing table (biasanya ke arah internet):</p>
<pre><code>Router(config)# ip route 0.0.0.0 0.0.0.0 10.10.10.1</code></pre>
<ul>
<li><strong>Kelebihan:</strong> hemat resource CPU dan bandwidth, aman, jalur terprediksi.</li>
<li><strong>Kekurangan:</strong> tidak otomatis menyesuaikan saat topologi berubah, merepotkan untuk jaringan besar.</li>
</ul>

<h3>Routing Dinamis</h3>
<p>Router saling bertukar informasi rute secara otomatis menggunakan routing protocol, sehingga routing table diperbarui sendiri ketika topologi berubah.</p>
<table>
<thead><tr><th>Kategori</th><th>Cara Kerja</th><th>Contoh</th></tr></thead>
<tbody>
<tr><td><strong>Distance Vector</strong></td><td>Mengirim seluruh tabel rute ke tetangga secara berkala; memilih jalur berdasarkan jumlah hop</td><td>RIP, IGRP</td></tr>
<tr><td><strong>Link State</strong></td><td>Setiap router memetakan topologi lengkap lalu menghitung jalur terpendek (algoritma Dijkstra)</td><td>OSPF, IS-IS</td></tr>
<tr><td><strong>Hybrid</strong></td><td>Menggabungkan keunggulan keduanya</td><td>EIGRP</td></tr>
</tbody>
</table>

<h3>RIP (Routing Information Protocol)</h3>
<ul>
<li>Termasuk <strong>distance vector</strong>, metric-nya adalah <strong>hop count</strong>.</li>
<li>Maksimal <strong>15 hop</strong>; hop ke-16 dianggap tak terjangkau (<em>unreachable</em>).</li>
<li><strong>Administrative Distance = 120</strong>.</li>
<li>RIPv2 mendukung VLSM/CIDR dan update secara multicast (224.0.0.9), RIPv1 tidak.</li>
</ul>
<pre><code>Router(config)# router rip
Router(config-router)# version 2
Router(config-router)# network 192.168.1.0
Router(config-router)# network 10.10.10.0
Router(config-router)# no auto-summary</code></pre>

<h3>OSPF (Open Shortest Path First)</h3>
<ul>
<li>Termasuk <strong>link state</strong>, metric-nya adalah <strong>cost</strong> (dihitung dari bandwidth).</li>
<li><strong>Administrative Distance = 110</strong>, tanpa batas hop count.</li>
<li>Konvergensi cepat dan mendukung pembagian <strong>area</strong> (area 0 = backbone).</li>
<li>Cocok untuk jaringan menengah hingga besar.</li>
</ul>
<pre><code>Router(config)# router ospf 1
Router(config-router)# network 192.168.1.0 0.0.0.255 area 0
Router(config-router)# network 10.10.10.0 0.0.0.3 area 0</code></pre>
<p class="catatan"><em>Perhatikan OSPF memakai <strong>wildcard mask</strong> (kebalikan subnet mask), contoh /24 ditulis 0.0.0.255.</em></p>

<h3>Administrative Distance (AD)</h3>
<p>AD menentukan tingkat kepercayaan terhadap sumber rute — makin kecil makin dipercaya.</p>
<table>
<thead><tr><th>Sumber Rute</th><th>AD</th></tr></thead>
<tbody>
<tr><td>Connected (langsung terhubung)</td><td>0</td></tr>
<tr><td>Static route</td><td>1</td></tr>
<tr><td>EIGRP</td><td>90</td></tr>
<tr><td>OSPF</td><td>110</td></tr>
<tr><td>RIP</td><td>120</td></tr>
</tbody>
</table>

<h3>Perintah Verifikasi</h3>
<pre><code>Router# show ip route
Router# show ip protocols
Router# show ip ospf neighbor
Router# ping 192.168.2.1</code></pre>
HTML;
    }

    private function dhcpDns(): string
    {
        return <<<'HTML'
<h3>Konsep DHCP</h3>
<p><strong>DHCP</strong> (<em>Dynamic Host Configuration Protocol</em>) memberikan konfigurasi jaringan secara otomatis kepada klien: alamat IP, subnet mask, default gateway, dan DNS server. Tanpa DHCP, setiap komputer harus dikonfigurasi manual sehingga rawan salah ketik dan <em>IP conflict</em>.</p>
<p>DHCP bekerja pada <strong>UDP port 67</strong> (server) dan <strong>UDP port 68</strong> (klien).</p>

<h3>Proses DORA</h3>
<ol>
<li><strong>Discover</strong> — klien mengirim broadcast mencari DHCP server yang tersedia.</li>
<li><strong>Offer</strong> — server menawarkan sebuah alamat IP beserta konfigurasi lainnya.</li>
<li><strong>Request</strong> — klien menyatakan menerima tawaran tersebut (broadcast, agar server lain tahu).</li>
<li><strong>Acknowledge</strong> — server mengonfirmasi dan mencatat masa sewa (<em>lease time</em>) alamat tersebut.</li>
</ol>

<h3>Istilah Penting DHCP</h3>
<ul>
<li><strong>Pool / Scope</strong> — rentang alamat IP yang boleh dibagikan.</li>
<li><strong>Lease time</strong> — durasi peminjaman IP sebelum harus diperbarui.</li>
<li><strong>Reservation</strong> — IP tetap yang diikat ke MAC address tertentu (misalnya untuk printer/server).</li>
<li><strong>Exclusion</strong> — alamat yang dikecualikan agar tidak dibagikan otomatis.</li>
</ul>

<h3>Konfigurasi DHCP Server di Debian</h3>
<pre><code>apt install isc-dhcp-server
nano /etc/dhcp/dhcpd.conf</code></pre>
<pre><code>subnet 192.168.10.0 netmask 255.255.255.0 {
    range 192.168.10.100 192.168.10.200;
    option routers 192.168.10.1;
    option domain-name-servers 192.168.10.1;
    option domain-name "sekolah.sch.id";
    default-lease-time 600;
    max-lease-time 7200;
}</code></pre>
<pre><code>systemctl restart isc-dhcp-server
systemctl status isc-dhcp-server</code></pre>

<h3>Konfigurasi DHCP di Cisco dan MikroTik</h3>
<pre><code># Cisco
Router(config)# ip dhcp pool LAB
Router(dhcp-config)# network 192.168.10.0 255.255.255.0
Router(dhcp-config)# default-router 192.168.10.1
Router(dhcp-config)# dns-server 8.8.8.8

# MikroTik
/ip pool add name=dhcp_pool ranges=192.168.10.100-192.168.10.200
/ip dhcp-server add address-pool=dhcp_pool interface=ether2 name=dhcp1 disabled=no</code></pre>

<h3>Konsep DNS</h3>
<p><strong>DNS</strong> (<em>Domain Name System</em>) menerjemahkan nama domain yang mudah diingat manusia (contoh <code>sekolah.sch.id</code>) menjadi alamat IP yang dipahami komputer. DNS bekerja pada <strong>port 53</strong> (UDP untuk query biasa, TCP untuk zone transfer).</p>

<h3>Hierarki DNS</h3>
<p>Root ( . ) &rarr; TLD (<code>.id</code>, <code>.com</code>) &rarr; Domain (<code>sekolah</code>) &rarr; Subdomain (<code>www</code>, <code>mail</code>).</p>

<h3>Jenis DNS Record</h3>
<table>
<thead><tr><th>Record</th><th>Fungsi</th></tr></thead>
<tbody>
<tr><td><strong>A</strong></td><td>Memetakan nama domain ke alamat IPv4</td></tr>
<tr><td><strong>AAAA</strong></td><td>Memetakan nama domain ke alamat IPv6</td></tr>
<tr><td><strong>CNAME</strong></td><td>Alias dari nama domain lain</td></tr>
<tr><td><strong>MX</strong></td><td>Menunjuk mail server untuk domain tersebut</td></tr>
<tr><td><strong>NS</strong></td><td>Menunjuk name server yang berwenang atas domain</td></tr>
<tr><td><strong>PTR</strong></td><td>Reverse lookup: dari IP ke nama domain</td></tr>
</tbody>
</table>

<h3>Cara Kerja Resolusi DNS</h3>
<ol>
<li>Browser memeriksa cache lokal dan file <code>hosts</code>.</li>
<li>Jika tidak ada, permintaan dikirim ke DNS resolver (biasanya milik ISP atau server lokal).</li>
<li>Resolver bertanya berjenjang ke root server &rarr; TLD server &rarr; authoritative name server.</li>
<li>Jawaban berupa alamat IP dikembalikan ke klien dan disimpan sementara di cache sesuai nilai TTL.</li>
</ol>

<h3>Konfigurasi DNS Server (BIND9) di Debian</h3>
<pre><code>apt install bind9
nano /etc/bind/named.conf.local</code></pre>
<pre><code>zone "sekolah.sch.id" {
    type master;
    file "/etc/bind/db.sekolah";
};
zone "10.168.192.in-addr.arpa" {
    type master;
    file "/etc/bind/db.192";
};</code></pre>
<p>Uji hasil konfigurasi dengan perintah <code>nslookup sekolah.sch.id</code> atau <code>dig sekolah.sch.id</code>.</p>
HTML;
    }

    private function webFtpMail(): string
    {
        return <<<'HTML'
<h3>Web Server</h3>
<p><strong>Web server</strong> adalah perangkat lunak yang melayani permintaan HTTP/HTTPS dari browser dan mengirimkan kembali halaman web. Aplikasi yang umum digunakan adalah <strong>Apache2</strong> dan <strong>Nginx</strong>.</p>
<ul>
<li><strong>Port:</strong> 80 (HTTP) dan 443 (HTTPS).</li>
<li><strong>Document root</strong> Apache di Debian: <code>/var/www/html</code>.</li>
<li><strong>Virtual host</strong> memungkinkan satu server melayani banyak domain sekaligus.</li>
</ul>
<pre><code>apt install apache2
nano /etc/apache2/sites-available/sekolah.conf</code></pre>
<pre><code>&lt;VirtualHost *:80&gt;
    ServerName www.sekolah.sch.id
    DocumentRoot /var/www/sekolah
    ErrorLog ${APACHE_LOG_DIR}/error.log
&lt;/VirtualHost&gt;</code></pre>
<pre><code>a2ensite sekolah.conf
systemctl reload apache2</code></pre>
<p>Untuk website dinamis (PHP), tambahkan paket <code>php</code> dan <code>libapache2-mod-php</code>. Sertifikat HTTPS dapat dipasang menggunakan SSL/TLS agar komunikasi terenkripsi.</p>

<h3>FTP Server</h3>
<p><strong>FTP</strong> (<em>File Transfer Protocol</em>) digunakan untuk mengunggah dan mengunduh berkas antara klien dan server. Aplikasi populer: <strong>vsftpd</strong> dan <strong>ProFTPD</strong>.</p>
<ul>
<li><strong>Port 21</strong> untuk kanal kontrol (perintah), <strong>port 20</strong> untuk kanal data pada mode aktif.</li>
<li><strong>Mode aktif</strong> — server yang membuka koneksi data ke klien; sering diblokir firewall klien.</li>
<li><strong>Mode pasif</strong> — klien yang membuka koneksi data ke server; lebih ramah terhadap firewall/NAT.</li>
<li><strong>FTPS/SFTP</strong> adalah versi aman; SFTP berjalan di atas SSH port 22.</li>
</ul>
<pre><code>apt install vsftpd
nano /etc/vsftpd.conf</code></pre>
<pre><code>anonymous_enable=NO
local_enable=YES
write_enable=YES
chroot_local_user=YES</code></pre>
<pre><code>systemctl restart vsftpd</code></pre>
<p>Pengujian dapat dilakukan lewat browser (<code>ftp://ip-server</code>) atau aplikasi FileZilla.</p>

<h3>Mail Server</h3>
<p><strong>Mail server</strong> menangani pengiriman dan penerimaan surat elektronik. Komponennya:</p>
<ul>
<li><strong>MTA</strong> (<em>Mail Transfer Agent</em>) — mengirim email antar server, contoh <strong>Postfix</strong>.</li>
<li><strong>MDA</strong> (<em>Mail Delivery Agent</em>) — menyimpan email ke kotak surat pengguna, contoh <strong>Dovecot</strong>.</li>
<li><strong>MUA</strong> (<em>Mail User Agent</em>) — aplikasi pembaca email, contoh Thunderbird atau webmail Roundcube.</li>
</ul>
<table>
<thead><tr><th>Protokol</th><th>Fungsi</th><th>Port</th></tr></thead>
<tbody>
<tr><td><strong>SMTP</strong></td><td>Mengirim email</td><td>25 (587 untuk submission, 465 SMTPS)</td></tr>
<tr><td><strong>POP3</strong></td><td>Mengunduh email lalu menghapusnya dari server</td><td>110 (995 dengan SSL)</td></tr>
<tr><td><strong>IMAP</strong></td><td>Membaca email dan tetap tersimpan di server (sinkron multi-perangkat)</td><td>143 (993 dengan SSL)</td></tr>
</tbody>
</table>
<pre><code>apt install postfix dovecot-imapd dovecot-pop3d
# Pilih "Internet Site" lalu isi domain, misal sekolah.sch.id</code></pre>
<p>Agar email antar domain dapat terkirim, domain harus memiliki <strong>MX record</strong> pada DNS yang menunjuk ke mail server tersebut.</p>

<h3>Ringkasan Pemetaan Layanan</h3>
<table>
<thead><tr><th>Kebutuhan</th><th>Layanan</th><th>Port</th></tr></thead>
<tbody>
<tr><td>Menampilkan website</td><td>Web server (Apache/Nginx)</td><td>80 / 443</td></tr>
<tr><td>Unggah &amp; unduh berkas</td><td>FTP server (vsftpd)</td><td>21 (dan 20)</td></tr>
<tr><td>Kirim &amp; terima email</td><td>Mail server (Postfix + Dovecot)</td><td>25, 110, 143</td></tr>
</tbody>
</table>
HTML;
    }

    private function firewallNatVpn(): string
    {
        return <<<'HTML'
<h3>Firewall</h3>
<p><strong>Firewall</strong> adalah sistem yang menyaring lalu lintas jaringan berdasarkan aturan keamanan, menentukan paket mana yang boleh lewat (<em>accept</em>) dan mana yang ditolak (<em>drop</em>/<em>reject</em>).</p>
<ul>
<li><strong>Packet filtering</strong> — memeriksa header paket (IP asal/tujuan, port, protokol).</li>
<li><strong>Stateful inspection</strong> — mengingat status koneksi, sehingga balasan dari koneksi yang sah otomatis diizinkan.</li>
<li><strong>Proxy / Application firewall</strong> — menyaring sampai level aplikasi (misalnya memblokir situs tertentu).</li>
</ul>
<p>Pada MikroTik dan iptables dikenal tiga <strong>chain</strong> utama:</p>
<table>
<thead><tr><th>Chain</th><th>Memfilter Traffic</th></tr></thead>
<tbody>
<tr><td><strong>input</strong></td><td>Menuju router itu sendiri (misalnya akses Winbox/SSH)</td></tr>
<tr><td><strong>forward</strong></td><td>Melewati router, dari satu jaringan ke jaringan lain</td></tr>
<tr><td><strong>output</strong></td><td>Berasal dari router menuju keluar</td></tr>
</tbody>
</table>
<pre><code># Izinkan koneksi yang sudah terbentuk
/ip firewall filter add chain=input action=accept connection-state=established,related
# Blokir akses klien ke situs tertentu
/ip firewall filter add chain=forward action=drop dst-address=103.10.10.10
# Blokir ping dari luar
/ip firewall filter add chain=input protocol=icmp in-interface=ether1 action=drop</code></pre>

<h3>NAT (Network Address Translation)</h3>
<p><strong>NAT</strong> menerjemahkan alamat IP privat menjadi IP publik (atau sebaliknya) sehingga banyak perangkat lokal dapat mengakses internet lewat satu IP publik.</p>
<table>
<thead><tr><th>Jenis</th><th>Cara Kerja</th><th>Penggunaan</th></tr></thead>
<tbody>
<tr><td><strong>Static NAT</strong></td><td>Satu IP privat dipetakan tetap ke satu IP publik</td><td>Server yang harus selalu dapat diakses</td></tr>
<tr><td><strong>Dynamic NAT</strong></td><td>IP privat dipetakan ke IP publik dari sebuah pool</td><td>Organisasi dengan beberapa IP publik</td></tr>
<tr><td><strong>PAT / NAT Overload (Masquerade)</strong></td><td>Banyak IP privat memakai satu IP publik, dibedakan dengan nomor port</td><td>Paling umum dipakai di sekolah/kantor/warnet</td></tr>
</tbody>
</table>
<pre><code># Masquerade: semua klien LAN bisa internet lewat 1 IP publik
/ip firewall nat add chain=srcnat out-interface=ether1 action=masquerade

# Port forwarding (DNAT): akses server lokal dari internet
/ip firewall nat add chain=dstnat protocol=tcp dst-port=80 \
    action=dst-nat to-addresses=192.168.1.10 to-ports=80</code></pre>
<p><strong>srcnat</strong> mengubah alamat <em>asal</em> (untuk traffic keluar), sedangkan <strong>dstnat</strong> mengubah alamat <em>tujuan</em> (untuk traffic masuk).</p>

<h3>VPN (Virtual Private Network)</h3>
<p><strong>VPN</strong> membuat jalur komunikasi pribadi yang terenkripsi (<em>tunnel</em>) melalui jaringan publik seperti internet, sehingga data tetap aman meskipun melewati jalur umum.</p>
<ul>
<li><strong>Remote Access VPN</strong> — pengguna individu (misal karyawan WFH) terhubung ke jaringan kantor.</li>
<li><strong>Site-to-Site VPN</strong> — menghubungkan dua jaringan kantor cabang menjadi seolah satu LAN.</li>
</ul>
<table>
<thead><tr><th>Protokol</th><th>Karakteristik</th></tr></thead>
<tbody>
<tr><td><strong>PPTP</strong></td><td>Mudah dikonfigurasi, tetapi enkripsinya lemah — tidak disarankan untuk data sensitif</td></tr>
<tr><td><strong>L2TP/IPSec</strong></td><td>Lebih aman karena dikombinasikan dengan enkripsi IPSec</td></tr>
<tr><td><strong>SSTP</strong></td><td>Berjalan di atas SSL port 443, mudah menembus firewall</td></tr>
<tr><td><strong>OpenVPN</strong></td><td>Open source, sangat aman dan fleksibel, memakai sertifikat</td></tr>
<tr><td><strong>WireGuard</strong></td><td>Modern, ringan, dan cepat dengan konfigurasi sederhana</td></tr>
</tbody>
</table>
<pre><code># Contoh mengaktifkan L2TP server di MikroTik
/interface l2tp-server server set enabled=yes use-ipsec=required ipsec-secret=RahasiaKuat
/ppp secret add name=karyawan password=Password123 service=l2tp profile=default</code></pre>

<h3>Penerapan Bersama</h3>
<p>Dalam praktiknya ketiga teknologi ini dipakai berdampingan: <strong>NAT</strong> agar seluruh klien dapat mengakses internet dengan satu IP publik, <strong>firewall</strong> untuk menyaring traffic yang tidak diinginkan, dan <strong>VPN</strong> agar akses jarak jauh ke server internal tetap terenkripsi dan aman.</p>
HTML;
    }
}
