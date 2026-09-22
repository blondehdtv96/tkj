<script setup>
import { ref, computed } from 'vue';
import Icon from '../Icon.vue';

const ip = ref('192.168.1.10');
const prefix = ref(24);
const error = ref('');
const showCidrTable = ref(false);

function ipToInt(octets) {
    return octets.reduce((acc, o) => acc * 256 + o, 0) >>> 0;
}

function intToIp(int) {
    return [24, 16, 8, 0].map((shift) => (int >>> shift) & 255).join('.');
}

function parseIp(value) {
    const parts = value.trim().split('.');
    if (parts.length !== 4) return null;
    const octets = parts.map(Number);
    if (octets.some((o) => Number.isNaN(o) || o < 0 || o > 255)) return null;
    return octets;
}

function isPrivateIp(octets) {
    const [a, b] = octets;
    if (a === 10) return true;
    if (a === 172 && b >= 16 && b <= 31) return true;
    if (a === 192 && b === 168) return true;
    return false;
}

const hasil = computed(() => {
    error.value = '';
    const octets = parseIp(ip.value);
    if (! octets) {
        error.value = 'Format IP tidak valid. Gunakan format seperti 192.168.1.10';
        return null;
    }
    if (prefix.value < 0 || prefix.value > 32) {
        error.value = 'Prefix harus antara 0 - 32.';
        return null;
    }

    const ipInt = ipToInt(octets);
    const maskInt = prefix.value === 0 ? 0 : (0xFFFFFFFF << (32 - prefix.value)) >>> 0;
    const networkInt = (ipInt & maskInt) >>> 0;
    const broadcastInt = (networkInt | (~maskInt >>> 0)) >>> 0;
    const totalHost = Math.pow(2, 32 - prefix.value);
    const usableHost = totalHost > 2 ? totalHost - 2 : totalHost;

    return {
        network: intToIp(networkInt),
        broadcast: intToIp(broadcastInt),
        subnetMask: intToIp(maskInt),
        wildcard: intToIp((~maskInt) >>> 0),
        firstHost: totalHost > 2 ? intToIp(networkInt + 1) : intToIp(networkInt),
        lastHost: totalHost > 2 ? intToIp(broadcastInt - 1) : intToIp(broadcastInt),
        totalHost,
        usableHost,
        kelas: octets[0] < 128 ? 'A' : octets[0] < 192 ? 'B' : octets[0] < 224 ? 'C' : octets[0] < 240 ? 'D' : 'E',
        privat: isPrivateIp(octets),
    };
});

const cidrTable = computed(() => {
    const rows = [];
    for (let p = 8; p <= 32; p++) {
        const maskInt = p === 0 ? 0 : (0xFFFFFFFF << (32 - p)) >>> 0;
        const total = Math.pow(2, 32 - p);
        let usable = total > 2 ? total - 2 : total;
        let catatan = '';
        if (p === 31) {
            usable = 2;
            catatan = 'Point-to-point (RFC 3021), tanpa network/broadcast';
        } else if (p === 32) {
            usable = 1;
            catatan = 'Host tunggal (host route)';
        }
        rows.push({
            prefix: p,
            mask: intToIp(maskInt),
            wildcard: intToIp((~maskInt) >>> 0),
            total,
            usable,
            catatan,
        });
    }
    return rows;
});

// Mode latihan soal acak.
const soalLatihan = ref(null);
const jawabanUser = ref('');
const feedback = ref(null);

function soalBaru() {
    const randomOctets = () => [10, 172, 192][Math.floor(Math.random() * 3)];
    const base = randomOctets();
    const o2 = Math.floor(Math.random() * 255);
    const o3 = Math.floor(Math.random() * 255);
    const o4 = Math.floor(Math.random() * 254) + 1;
    const randomPrefix = [24, 25, 26, 27, 28][Math.floor(Math.random() * 5)];

    soalLatihan.value = { ip: `${base}.${o2}.${o3}.${o4}`, prefix: randomPrefix };
    jawabanUser.value = '';
    feedback.value = null;
}

function cekJawaban() {
    const octets = parseIp(soalLatihan.value.ip);
    const ipInt = ipToInt(octets);
    const maskInt = (0xFFFFFFFF << (32 - soalLatihan.value.prefix)) >>> 0;
    const networkInt = (ipInt & maskInt) >>> 0;
    const jawabanBenar = intToIp(networkInt);

    const correct = jawabanUser.value.trim() === jawabanBenar;
    feedback.value = {
        correct,
        text: correct ? 'Benar!' : `Kurang tepat. Network address yang benar: ${jawabanBenar}`,
    };
}

soalBaru();
</script>

<template>
    <div class="space-y-6">
        <div class="rounded-xl bg-white dark:bg-slate-800 p-5 shadow-sm">
            <h2 class="font-semibold text-primary-900 dark:text-white mb-3">Kalkulator Subnetting</h2>

            <div class="flex flex-wrap gap-3 items-end">
                <div>
                    <label class="block text-xs font-medium text-slate-500 dark:text-slate-400">Alamat IP</label>
                    <input v-model="ip" type="text" class="mt-1 rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-700" />
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-500 dark:text-slate-400">Prefix (CIDR)</label>
                    <div class="mt-1 flex items-center gap-1">
                        <span>/</span>
                        <input v-model.number="prefix" type="number" min="0" max="32" class="w-20 rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-700" />
                    </div>
                </div>
            </div>

            <p v-if="error" class="mt-3 text-sm text-red-600">{{ error }}</p>

            <div v-if="hasil" class="mt-4 grid grid-cols-2 sm:grid-cols-3 gap-3 text-sm">
                <div class="rounded-lg bg-slate-50 dark:bg-slate-700/50 p-3">
                    <p class="text-xs text-slate-400">Kelas</p>
                    <p class="font-mono font-semibold text-slate-700 dark:text-slate-200">
                        {{ hasil.kelas }}
                        <span class="ml-1 rounded-full px-1.5 py-0.5 text-[10px] font-sans font-semibold" :class="hasil.privat ? 'bg-teal-100 text-teal-700 dark:bg-teal-900 dark:text-teal-200' : 'bg-amber-100 text-amber-700 dark:bg-amber-900 dark:text-amber-200'">
                            {{ hasil.privat ? 'Private' : 'Public' }}
                        </span>
                    </p>
                </div>
                <div class="rounded-lg bg-slate-50 dark:bg-slate-700/50 p-3">
                    <p class="text-xs text-slate-400">Subnet Mask</p>
                    <p class="font-mono font-semibold text-slate-700 dark:text-slate-200">{{ hasil.subnetMask }}</p>
                </div>
                <div class="rounded-lg bg-slate-50 dark:bg-slate-700/50 p-3">
                    <p class="text-xs text-slate-400">Wildcard</p>
                    <p class="font-mono font-semibold text-slate-700 dark:text-slate-200">{{ hasil.wildcard }}</p>
                </div>
                <div class="rounded-lg bg-teal-50 dark:bg-teal-900/30 p-3">
                    <p class="text-xs text-teal-600 dark:text-teal-300">Network Address</p>
                    <p class="font-mono font-semibold text-teal-800 dark:text-teal-200">{{ hasil.network }}</p>
                </div>
                <div class="rounded-lg bg-teal-50 dark:bg-teal-900/30 p-3">
                    <p class="text-xs text-teal-600 dark:text-teal-300">Broadcast Address</p>
                    <p class="font-mono font-semibold text-teal-800 dark:text-teal-200">{{ hasil.broadcast }}</p>
                </div>
                <div class="rounded-lg bg-slate-50 dark:bg-slate-700/50 p-3">
                    <p class="text-xs text-slate-400">Range Host</p>
                    <p class="font-mono font-semibold text-slate-700 dark:text-slate-200">{{ hasil.firstHost }} - {{ hasil.lastHost }}</p>
                </div>
                <div class="rounded-lg bg-slate-50 dark:bg-slate-700/50 p-3">
                    <p class="text-xs text-slate-400">Jumlah Host</p>
                    <p class="font-mono font-semibold text-slate-700 dark:text-slate-200">{{ hasil.usableHost }} usable</p>
                </div>
            </div>
        </div>

        <div class="rounded-xl bg-white dark:bg-slate-800 p-5 shadow-sm">
            <button
                class="flex w-full items-center justify-between text-left"
                @click="showCidrTable = !showCidrTable"
            >
                <h2 class="font-semibold text-primary-900 dark:text-white">Tabel Referensi CIDR (/8 - /32)</h2>
                <Icon :name="showCidrTable ? 'chevron-up' : 'chevron-down'" :size="18" class="text-slate-400" />
            </button>

            <div v-if="showCidrTable" class="mt-3 max-h-72 overflow-y-auto overflow-x-auto rounded-lg border border-slate-100 dark:border-slate-700">
                <table class="min-w-full text-xs">
                    <thead class="sticky top-0 bg-slate-50 dark:bg-slate-700 text-slate-500 dark:text-slate-300">
                        <tr>
                            <th class="px-3 py-2 text-left font-medium">Prefix</th>
                            <th class="px-3 py-2 text-left font-medium">Subnet Mask</th>
                            <th class="px-3 py-2 text-left font-medium">Wildcard</th>
                            <th class="px-3 py-2 text-left font-medium">Total IP</th>
                            <th class="px-3 py-2 text-left font-medium">Usable Host</th>
                            <th class="px-3 py-2 text-left font-medium">Catatan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                        <tr
                            v-for="row in cidrTable"
                            :key="row.prefix"
                            class="font-mono"
                            :class="row.prefix === prefix ? 'bg-primary-50 dark:bg-primary-900/30' : ''"
                        >
                            <td class="px-3 py-1.5">/{{ row.prefix }}</td>
                            <td class="px-3 py-1.5">{{ row.mask }}</td>
                            <td class="px-3 py-1.5">{{ row.wildcard }}</td>
                            <td class="px-3 py-1.5">{{ row.total.toLocaleString('id-ID') }}</td>
                            <td class="px-3 py-1.5">{{ row.usable.toLocaleString('id-ID') }}</td>
                            <td class="px-3 py-1.5 font-sans text-slate-400">{{ row.catatan }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="rounded-xl bg-white dark:bg-slate-800 p-5 shadow-sm">
            <div class="flex items-center justify-between mb-3">
                <h2 class="font-semibold text-primary-900 dark:text-white">Latihan Soal</h2>
                <button class="text-sm text-primary-600 hover:underline" @click="soalBaru">Soal Baru</button>
            </div>

            <p v-if="soalLatihan" class="text-sm text-slate-600 dark:text-slate-300">
                Tentukan <strong>network address</strong> dari <span class="font-mono">{{ soalLatihan.ip }}/{{ soalLatihan.prefix }}</span>
            </p>

            <div class="mt-3 flex flex-wrap gap-2">
                <input
                    v-model="jawabanUser"
                    type="text"
                    placeholder="mis. 192.168.1.0"
                    class="rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-700"
                    @keyup.enter="cekJawaban"
                />
                <button class="rounded-lg bg-primary-600 px-4 py-2 text-sm text-white hover:bg-primary-700" @click="cekJawaban">
                    Cek Jawaban
                </button>
            </div>

            <p v-if="feedback" class="mt-2 flex items-center gap-1.5 text-sm" :class="feedback.correct ? 'text-teal-600' : 'text-red-500'">
                <Icon :name="feedback.correct ? 'check-circle' : 'x-circle'" :size="16" />
                {{ feedback.text }}
            </p>
        </div>
    </div>
</template>
