<script setup>
import { ref } from 'vue';

const layers = [
    {
        no: 7,
        nama: 'Application',
        deskripsi: 'Antarmuka langsung ke aplikasi pengguna untuk mengakses layanan jaringan seperti browsing, email, dan transfer file.',
        analogi: 'Seperti aplikasi surat/email yang kamu pakai sehari-hari — pengguna hanya berinteraksi di sini tanpa perlu tahu proses pengiriman di baliknya.',
        protokol: ['HTTP (80)', 'HTTPS (443)', 'FTP (20/21)', 'SMTP (25)', 'DNS (53)', 'SSH (22)', 'Telnet (23)', 'DHCP (67/68)', 'POP3 (110)', 'IMAP (143)'],
        perangkat: ['Gateway aplikasi', 'Firewall Layer 7 (WAF)'],
    },
    {
        no: 6,
        nama: 'Presentation',
        deskripsi: 'Menerjemahkan, mengenkripsi, dan mengompresi data agar formatnya dapat dipahami oleh layer Application di sisi penerima.',
        analogi: 'Seperti penerjemah yang mengubah isi surat ke format yang dipahami penerima, termasuk menyandikan (enkripsi) atau memampatkan (kompresi) datanya.',
        protokol: ['SSL/TLS', 'JPEG', 'PNG', 'MPEG', 'ASCII', 'Unicode'],
        perangkat: ['Gateway'],
    },
    {
        no: 5,
        nama: 'Session',
        deskripsi: 'Membuka, mengelola, dan menutup sesi/koneksi komunikasi antar dua aplikasi pada host yang berbeda.',
        analogi: 'Seperti operator telepon yang menyambungkan, menjaga, lalu memutuskan panggilan antara dua pihak yang sedang berkomunikasi.',
        protokol: ['NetBIOS', 'RPC', 'PPTP', 'SIP (VoIP)'],
        perangkat: ['Gateway'],
    },
    {
        no: 4,
        nama: 'Transport',
        deskripsi: 'Menyediakan transfer data end-to-end, termasuk segmentasi, kontrol aliran, dan nomor port untuk membedakan tiap aplikasi.',
        analogi: 'Seperti memilih jenis pengiriman paket: TCP seperti kurir dengan tanda terima (andal, ada konfirmasi), UDP seperti kirim kilat tanpa tanda terima (cepat, tanpa jaminan sampai).',
        protokol: ['TCP', 'UDP', 'Nomor Port (0-65535)'],
        perangkat: ['Firewall'],
    },
    {
        no: 3,
        nama: 'Network',
        deskripsi: 'Menentukan pengalamatan logis (IP) dan menentukan rute (routing) paket data dari satu jaringan ke jaringan lain.',
        analogi: 'Seperti kode pos dan alamat pada amplop — menentukan ke jaringan mana sebuah paket data harus dikirim melalui jalur terbaik.',
        protokol: ['IP (IPv4/IPv6)', 'ICMP', 'OSPF', 'RIP', 'EIGRP'],
        perangkat: ['Router', 'Layer 3 Switch'],
        catatan: 'ARP (Address Resolution Protocol) bekerja di batas antara Layer 2 dan 3 untuk menerjemahkan alamat IP menjadi alamat MAC.',
    },
    {
        no: 2,
        nama: 'Data Link',
        deskripsi: 'Mengatur pengalamatan fisik (MAC) dan transfer frame antar perangkat dalam satu segmen jaringan lokal.',
        analogi: 'Seperti nama dan nomor rumah di satu kompleks yang sama — mengatur pengiriman frame antar perangkat dalam satu jaringan lokal menggunakan MAC address.',
        protokol: ['Ethernet', 'PPP', 'VLAN (802.1Q)', 'MAC Address'],
        perangkat: ['Switch', 'Bridge', 'NIC'],
    },
    {
        no: 1,
        nama: 'Physical',
        deskripsi: 'Mentransmisikan bit mentah (0 dan 1) melalui media fisik seperti kabel tembaga, fiber optik, atau gelombang radio.',
        analogi: 'Seperti jalan raya dan kendaraan fisik yang membawa surat — media dan sinyal listrik/cahaya yang benar-benar mengangkut data.',
        protokol: ['Ethernet fisik (10/100/1000BASE-T)', 'Fiber Optik', 'USB', 'Bluetooth'],
        perangkat: ['Hub', 'Kabel UTP', 'Fiber optik', 'Access Point', 'Repeater'],
    },
];

const aktif = ref(layers[0]);
</script>

<template>
    <div class="rounded-xl bg-white dark:bg-slate-800 p-5 shadow-sm">
        <h2 class="font-semibold text-primary-900 dark:text-white mb-4">7 Layer OSI</h2>

        <div class="grid gap-4 lg:grid-cols-[220px_1fr]">
            <div class="space-y-1.5">
                <button
                    v-for="layer in layers"
                    :key="layer.no"
                    class="w-full rounded-lg px-3 py-2.5 text-left text-sm font-medium transition-colors"
                    :class="aktif.no === layer.no
                        ? 'bg-primary-600 text-white'
                        : 'bg-slate-50 dark:bg-slate-700/50 text-slate-700 dark:text-slate-200 hover:bg-primary-50 dark:hover:bg-slate-700'"
                    @click="aktif = layer"
                >
                    Layer {{ layer.no }} &middot; {{ layer.nama }}
                </button>
            </div>

            <div class="rounded-lg bg-slate-50 dark:bg-slate-700/50 p-5">
                <h3 class="text-lg font-semibold text-primary-900 dark:text-white">
                    Layer {{ aktif.no }}: {{ aktif.nama }}
                </h3>
                <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">{{ aktif.deskripsi }}</p>

                <p class="mt-3 rounded-lg bg-primary-50 dark:bg-primary-900/30 px-3 py-2 text-xs text-primary-700 dark:text-primary-300">
                    <strong>Analogi:</strong> {{ aktif.analogi }}
                </p>

                <div class="mt-4 grid sm:grid-cols-2 gap-4">
                    <div>
                        <h4 class="text-xs font-semibold uppercase text-slate-400 mb-1.5">Protokol Terkait</h4>
                        <div class="flex flex-wrap gap-1.5">
                            <span v-for="p in aktif.protokol" :key="p" class="rounded-full bg-primary-100 dark:bg-primary-900 text-primary-700 dark:text-primary-200 text-xs font-medium px-2.5 py-1">
                                {{ p }}
                            </span>
                        </div>
                    </div>
                    <div>
                        <h4 class="text-xs font-semibold uppercase text-slate-400 mb-1.5">Perangkat Terkait</h4>
                        <div class="flex flex-wrap gap-1.5">
                            <span v-for="p in aktif.perangkat" :key="p" class="rounded-full bg-teal-100 dark:bg-teal-900 text-teal-700 dark:text-teal-200 text-xs font-medium px-2.5 py-1">
                                {{ p }}
                            </span>
                        </div>
                    </div>
                </div>

                <p v-if="aktif.catatan" class="mt-4 text-xs text-slate-500 dark:text-slate-400">
                    <strong>Catatan:</strong> {{ aktif.catatan }}
                </p>
            </div>
        </div>
    </div>
</template>
