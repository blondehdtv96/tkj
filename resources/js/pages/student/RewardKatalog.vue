<script setup>
import { ref, onMounted, computed } from 'vue';
import { useGamifikasiStore } from '../../stores/gamifikasi';
import { useToastStore } from '../../stores/toast';
import SkeletonBlock from '../../components/SkeletonBlock.vue';
import Icon from '../../components/Icon.vue';

const gamifikasi = useGamifikasiStore();
const toast = useToastStore();

const loading = ref(true);
const tab = ref('katalog');
const rewardTerpilih = ref(null);
const catatan = ref('');
const mengajukan = ref(false);

const totalPoin = computed(() => gamifikasi.ringkasan?.total_poin ?? 0);

const statusTone = {
    pending: 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-200',
    approved: 'bg-primary-100 text-primary-700 dark:bg-primary-900/40 dark:text-primary-200',
    completed: 'bg-teal-100 text-teal-700 dark:bg-teal-900/40 dark:text-teal-200',
    rejected: 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-200',
};

async function load() {
    loading.value = true;
    try {
        await Promise.all([
            gamifikasi.fetchRingkasan(),
            gamifikasi.fetchRewards(),
            gamifikasi.fetchRedemptions(),
        ]);
        gamifikasi.sinkronkanPoinAuth();
    } finally {
        loading.value = false;
    }
}

function bukaForm(reward) {
    rewardTerpilih.value = reward;
    catatan.value = '';
}

function tutupForm() {
    rewardTerpilih.value = null;
    catatan.value = '';
}

async function ajukan() {
    mengajukan.value = true;
    try {
        await gamifikasi.ajukanRedeem({
            reward_id: rewardTerpilih.value.id,
            catatan_siswa: catatan.value || null,
        });
        await Promise.all([gamifikasi.fetchRewards(), gamifikasi.fetchRedemptions()]);
        toast.success('Pengajuan penukaran dikirim. Tunggu verifikasi guru atau admin.');
        tab.value = 'riwayat';
        tutupForm();
    } finally {
        mengajukan.value = false;
    }
}

function formatTanggal(nilai) {
    return new Date(nilai).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
}

onMounted(load);
</script>

<template>
    <div class="space-y-5">
        <div class="animate-fade-up flex flex-wrap items-start justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold text-primary-900 dark:text-white">Tukar Poin</h1>
                <p class="mt-1 text-slate-500 dark:text-slate-400">
                    Tukarkan poin belajarmu dengan barang yang disediakan sekolah.
                </p>
            </div>

            <div class="flex items-center gap-2 rounded-xl bg-white px-4 py-2.5 shadow-sm dark:bg-slate-800">
                <Icon name="coin" :size="20" class="text-amber-500" />
                <div>
                    <p class="text-[11px] font-medium uppercase leading-none text-slate-400">Poin Kamu</p>
                    <p class="mt-1 text-lg font-bold leading-none tabular-nums text-primary-900 dark:text-white">
                        {{ totalPoin.toLocaleString('id-ID') }}
                    </p>
                </div>
            </div>
        </div>

        <div class="flex gap-2 border-b border-slate-200 dark:border-slate-700">
            <button
                v-for="item in [{ key: 'katalog', label: 'Katalog Reward' }, { key: 'riwayat', label: 'Riwayat Penukaran' }]"
                :key="item.key"
                class="-mb-px border-b-2 px-3 py-2 text-sm font-medium transition-colors"
                :class="tab === item.key
                    ? 'border-teal-500 text-teal-600 dark:text-teal-400'
                    : 'border-transparent text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200'"
                @click="tab = item.key"
            >
                {{ item.label }}
            </button>
        </div>

        <SkeletonBlock v-if="loading" />

        <div v-else-if="tab === 'katalog'" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <div
                v-for="reward in gamifikasi.rewards"
                :key="reward.id"
                class="animate-fade-up flex flex-col overflow-hidden rounded-xl bg-white shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md dark:bg-slate-800"
            >
                <div class="flex h-32 items-center justify-center bg-gradient-to-br from-primary-50 to-teal-50 dark:from-slate-700 dark:to-slate-700/50">
                    <img v-if="reward.gambar_url" :src="reward.gambar_url" :alt="reward.nama_barang" class="h-full w-full object-cover">
                    <Icon v-else name="gift" :size="40" class="text-primary-300 dark:text-slate-500" />
                </div>

                <div class="flex flex-1 flex-col p-4">
                    <h3 class="font-semibold text-primary-900 dark:text-white">{{ reward.nama_barang }}</h3>
                    <p class="mt-1 flex-1 text-sm leading-snug text-slate-500 dark:text-slate-400">{{ reward.deskripsi }}</p>

                    <div class="mt-3 flex items-center justify-between gap-2">
                        <span class="inline-flex items-center gap-1.5 text-sm font-bold text-amber-600 dark:text-amber-400">
                            <Icon name="coin" :size="15" />
                            {{ reward.harga_poin.toLocaleString('id-ID') }}
                        </span>
                        <span
                            class="rounded-full px-2 py-0.5 text-[11px] font-semibold"
                            :class="reward.stok > 0
                                ? 'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300'
                                : 'bg-red-100 text-red-600 dark:bg-red-900/40 dark:text-red-300'"
                        >
                            Stok {{ reward.stok }}
                        </span>
                    </div>

                    <button
                        class="mt-3 w-full rounded-lg bg-teal-600 px-3 py-2 text-sm font-medium text-white transition-colors hover:bg-teal-700 disabled:cursor-not-allowed disabled:bg-slate-300 disabled:text-slate-500 dark:disabled:bg-slate-700 dark:disabled:text-slate-400"
                        :disabled="!reward.tersedia || reward.poin_kurang > 0"
                        @click="bukaForm(reward)"
                    >
                        <template v-if="!reward.tersedia">Stok Habis</template>
                        <template v-else-if="reward.poin_kurang > 0">Kurang {{ reward.poin_kurang.toLocaleString('id-ID') }} poin</template>
                        <template v-else>Tukar Sekarang</template>
                    </button>
                </div>
            </div>

            <p v-if="gamifikasi.rewards.length === 0" class="text-sm text-slate-400">
                Belum ada reward yang dibuka sekolah.
            </p>
        </div>

        <ul v-else class="space-y-2">
            <li
                v-for="item in gamifikasi.redemptions"
                :key="item.id"
                class="animate-fade-up rounded-xl bg-white p-4 shadow-sm dark:bg-slate-800"
            >
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div class="min-w-0">
                        <p class="font-medium text-primary-900 dark:text-white">{{ item.reward?.nama_barang }}</p>
                        <p class="mt-0.5 text-xs text-slate-400">
                            Diajukan {{ formatTanggal(item.created_at) }} &middot; {{ item.jumlah_poin.toLocaleString('id-ID') }} poin
                        </p>
                    </div>
                    <span class="shrink-0 rounded-full px-2.5 py-1 text-xs font-semibold" :class="statusTone[item.status]">
                        {{ item.status_label }}
                    </span>
                </div>

                <p v-if="item.catatan_petugas" class="mt-2 rounded-lg bg-slate-50 px-3 py-2 text-sm text-slate-600 dark:bg-slate-700/50 dark:text-slate-300">
                    Catatan petugas: {{ item.catatan_petugas }}
                </p>
            </li>

            <li v-if="gamifikasi.redemptions.length === 0" class="py-4 text-center text-sm text-slate-400">
                Belum ada pengajuan penukaran.
            </li>
        </ul>

        <!-- Form pengajuan penukaran -->
        <div v-if="rewardTerpilih" class="fixed inset-0 z-50 flex items-end justify-center bg-black/50 p-4 sm:items-center" @click.self="tutupForm">
            <div class="w-full max-w-md rounded-xl bg-white p-5 shadow-xl dark:bg-slate-800">
                <h2 class="flex items-center gap-2 text-lg font-semibold text-primary-900 dark:text-white">
                    <Icon name="gift" :size="18" class="text-teal-500" />
                    Ajukan Penukaran
                </h2>

                <div class="mt-4 space-y-1 rounded-lg bg-slate-50 p-3 text-sm dark:bg-slate-700/50">
                    <p class="font-medium text-slate-700 dark:text-slate-200">{{ rewardTerpilih.nama_barang }}</p>
                    <p class="text-slate-500 dark:text-slate-400">
                        Biaya {{ rewardTerpilih.harga_poin.toLocaleString('id-ID') }} poin &middot;
                        sisa poinmu menjadi {{ (totalPoin - rewardTerpilih.harga_poin).toLocaleString('id-ID') }}
                    </p>
                </div>

                <label class="mt-4 block">
                    <span class="text-xs font-medium text-slate-500 dark:text-slate-400">Catatan (opsional)</span>
                    <textarea
                        v-model="catatan"
                        rows="3"
                        maxlength="255"
                        placeholder="Contoh: ukuran kaos L, diambil hari Jumat."
                        class="mt-1 w-full rounded-lg border-slate-300 text-sm dark:border-slate-600 dark:bg-slate-700"
                    />
                </label>

                <p class="mt-3 text-xs leading-relaxed text-slate-400">
                    Poin langsung ditahan saat pengajuan dikirim, dan dikembalikan bila penukaran ditolak.
                </p>

                <div class="mt-4 flex justify-end gap-2">
                    <button
                        class="rounded-lg px-4 py-2 text-sm font-medium text-slate-600 transition-colors hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-700"
                        @click="tutupForm"
                    >
                        Batal
                    </button>
                    <button
                        :disabled="mengajukan"
                        class="rounded-lg bg-teal-600 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-teal-700 disabled:opacity-50"
                        @click="ajukan"
                    >
                        {{ mengajukan ? 'Mengirim...' : 'Kirim Pengajuan' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
