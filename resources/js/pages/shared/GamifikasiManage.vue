<script setup>
import { ref, onMounted, computed } from 'vue';
import axios from 'axios';
import { useGamifikasiStore } from '../../stores/gamifikasi';
import { useToastStore } from '../../stores/toast';
import { useConfirmStore } from '../../stores/confirm';
import SkeletonBlock from '../../components/SkeletonBlock.vue';
import StatCard from '../../components/StatCard.vue';
import Icon from '../../components/Icon.vue';

const gamifikasi = useGamifikasiStore();
const toast = useToastStore();
const confirmStore = useConfirmStore();

const tab = ref('verifikasi');
const loading = ref(true);
const memproses = ref(null);

const statusFilter = ref('pending');
const daftarRedemption = ref([]);

const kosongForm = () => ({ id: null, nama_barang: '', deskripsi: '', harga_poin: 100, stok: 10, aktif: true });
const form = ref(kosongForm());
const gambar = ref(null);
const saving = ref(false);

const statusTone = {
    pending: 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-200',
    approved: 'bg-primary-100 text-primary-700 dark:bg-primary-900/40 dark:text-primary-200',
    completed: 'bg-teal-100 text-teal-700 dark:bg-teal-900/40 dark:text-teal-200',
    rejected: 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-200',
};

const statusOptions = [
    { value: 'pending', label: 'Menunggu Verifikasi' },
    { value: 'approved', label: 'Disetujui' },
    { value: 'completed', label: 'Sudah Diambil' },
    { value: 'rejected', label: 'Ditolak' },
    { value: '', label: 'Semua Status' },
];

const menunggu = computed(() => daftarRedemption.value.filter((item) => item.status === 'pending').length);
const totalStok = computed(() => gamifikasi.rewards.reduce((total, reward) => total + reward.stok, 0));
const rewardAktif = computed(() => gamifikasi.rewards.filter((reward) => reward.aktif).length);

async function loadRedemptions() {
    const hasil = await gamifikasi.fetchRedemptions(statusFilter.value ? { status: statusFilter.value } : {});
    daftarRedemption.value = hasil.data;
}

async function load() {
    loading.value = true;
    try {
        await Promise.all([gamifikasi.fetchRewards(), loadRedemptions()]);
    } finally {
        loading.value = false;
    }
}

async function proses(item, status) {
    let catatan = null;

    if (status === 'rejected') {
        catatan = window.prompt('Alasan penolakan (wajib diisi):');
        if (! catatan) return;
    }

    memproses.value = item.id;
    try {
        await gamifikasi.ubahStatusRedeem(item.id, { status, catatan_petugas: catatan });
        await loadRedemptions();
        toast.success(
            status === 'rejected'
                ? 'Penukaran ditolak, poin siswa sudah dikembalikan.'
                : 'Status penukaran diperbarui.',
        );
    } finally {
        memproses.value = null;
    }
}

function editReward(reward) {
    form.value = {
        id: reward.id,
        nama_barang: reward.nama_barang,
        deskripsi: reward.deskripsi ?? '',
        harga_poin: reward.harga_poin,
        stok: reward.stok,
        aktif: reward.aktif,
    };
    gambar.value = null;
    tab.value = 'reward';
}

function resetForm() {
    form.value = kosongForm();
    gambar.value = null;
}

async function simpanReward() {
    saving.value = true;
    try {
        const payload = new FormData();
        payload.append('nama_barang', form.value.nama_barang);
        payload.append('deskripsi', form.value.deskripsi ?? '');
        payload.append('harga_poin', form.value.harga_poin);
        payload.append('stok', form.value.stok);
        payload.append('aktif', form.value.aktif ? 1 : 0);

        if (gambar.value) {
            payload.append('gambar', gambar.value);
        }

        const url = form.value.id ? `/api/gamifikasi/rewards/${form.value.id}` : '/api/gamifikasi/rewards';
        await axios.post(url, payload);

        await gamifikasi.fetchRewards();
        toast.success(form.value.id ? 'Reward diperbarui.' : 'Reward ditambahkan ke katalog.');
        resetForm();
    } finally {
        saving.value = false;
    }
}

async function hapusReward(reward) {
    const pesan = `Hapus reward "${reward.nama_barang}" dari katalog?`;
    if (! (await confirmStore.ask(pesan, { title: 'Hapus Reward' }))) return;

    const { data } = await axios.delete(`/api/gamifikasi/rewards/${reward.id}`);
    await gamifikasi.fetchRewards();
    toast.success(data.message);
}

function formatTanggal(nilai) {
    return new Date(nilai).toLocaleString('id-ID', {
        day: '2-digit',
        month: 'short',
        hour: '2-digit',
        minute: '2-digit',
    });
}

onMounted(load);
</script>

<template>
    <div class="space-y-5">
        <div class="animate-fade-up">
            <h1 class="text-2xl font-semibold text-primary-900 dark:text-white">Gamifikasi</h1>
            <p class="mt-1 text-slate-500 dark:text-slate-400">
                Kelola katalog reward dan verifikasi penukaran poin siswa.
            </p>
        </div>

        <div class="grid gap-4 sm:grid-cols-3">
            <StatCard icon="clipboard" tone="amber" label="Menunggu Verifikasi" :value="loading ? '-' : menunggu" />
            <StatCard icon="gift" tone="teal" label="Reward Aktif" :value="loading ? '-' : rewardAktif" />
            <StatCard icon="briefcase" tone="primary" label="Total Stok" :value="loading ? '-' : totalStok" />
        </div>

        <div class="flex gap-2 border-b border-slate-200 dark:border-slate-700">
            <button
                v-for="item in [{ key: 'verifikasi', label: 'Verifikasi Penukaran' }, { key: 'reward', label: 'Kelola Reward' }]"
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

        <!-- Verifikasi penukaran -->
        <template v-else-if="tab === 'verifikasi'">
            <div class="flex flex-wrap items-end gap-3 rounded-xl bg-white p-4 shadow-sm dark:bg-slate-800">
                <div>
                    <label class="block text-xs font-medium text-slate-500 dark:text-slate-400">Status</label>
                    <select
                        v-model="statusFilter"
                        class="mt-1 rounded-lg border-slate-300 text-sm dark:border-slate-600 dark:bg-slate-700"
                        @change="loadRedemptions"
                    >
                        <option v-for="item in statusOptions" :key="item.value" :value="item.value">{{ item.label }}</option>
                    </select>
                </div>
            </div>

            <ul class="space-y-2">
                <li
                    v-for="item in daftarRedemption"
                    :key="item.id"
                    class="animate-fade-up rounded-xl bg-white p-4 shadow-sm dark:bg-slate-800"
                >
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="font-medium text-primary-900 dark:text-white">
                                {{ item.user?.name }}
                                <span class="text-sm font-normal text-slate-400">
                                    &middot; {{ item.user?.kelas?.nama_rombel ?? 'Tanpa kelas' }}
                                </span>
                            </p>
                            <p class="mt-0.5 text-sm text-slate-600 dark:text-slate-300">
                                {{ item.reward?.nama_barang }} &middot;
                                <span class="font-semibold text-amber-600 dark:text-amber-400">
                                    {{ item.jumlah_poin.toLocaleString('id-ID') }} poin
                                </span>
                            </p>
                            <p class="mt-0.5 text-xs text-slate-400">Diajukan {{ formatTanggal(item.created_at) }}</p>
                            <p v-if="item.catatan_siswa" class="mt-2 rounded-lg bg-slate-50 px-3 py-2 text-sm text-slate-600 dark:bg-slate-700/50 dark:text-slate-300">
                                Catatan siswa: {{ item.catatan_siswa }}
                            </p>
                        </div>

                        <span class="shrink-0 rounded-full px-2.5 py-1 text-xs font-semibold" :class="statusTone[item.status]">
                            {{ item.status_label }}
                        </span>
                    </div>

                    <div v-if="item.status === 'pending' || item.status === 'approved'" class="mt-3 flex flex-wrap gap-2">
                        <button
                            v-if="item.status === 'pending'"
                            :disabled="memproses === item.id"
                            class="rounded-lg bg-primary-600 px-3 py-1.5 text-sm font-medium text-white transition-colors hover:bg-primary-700 disabled:opacity-50"
                            @click="proses(item, 'approved')"
                        >
                            Setujui
                        </button>
                        <button
                            :disabled="memproses === item.id"
                            class="rounded-lg bg-teal-600 px-3 py-1.5 text-sm font-medium text-white transition-colors hover:bg-teal-700 disabled:opacity-50"
                            @click="proses(item, 'completed')"
                        >
                            Tandai Sudah Diambil
                        </button>
                        <button
                            :disabled="memproses === item.id"
                            class="rounded-lg px-3 py-1.5 text-sm font-medium text-red-600 transition-colors hover:bg-red-50 disabled:opacity-50 dark:text-red-400 dark:hover:bg-red-900/20"
                            @click="proses(item, 'rejected')"
                        >
                            Tolak
                        </button>
                    </div>

                    <p v-else-if="item.catatan_petugas" class="mt-2 text-sm text-slate-500 dark:text-slate-400">
                        Catatan petugas: {{ item.catatan_petugas }}
                    </p>
                </li>

                <li v-if="daftarRedemption.length === 0" class="rounded-xl bg-white py-6 text-center text-sm text-slate-400 shadow-sm dark:bg-slate-800">
                    Tidak ada pengajuan pada filter ini.
                </li>
            </ul>
        </template>

        <!-- Kelola reward -->
        <template v-else>
            <form class="animate-fade-up space-y-4 rounded-xl bg-white p-5 shadow-sm dark:bg-slate-800" @submit.prevent="simpanReward">
                <h2 class="flex items-center gap-2 text-sm font-semibold text-primary-900 dark:text-white">
                    <Icon name="gift" :size="16" class="text-primary-500" />
                    {{ form.id ? 'Ubah Reward' : 'Tambah Reward' }}
                </h2>

                <div class="grid gap-4 sm:grid-cols-2">
                    <label class="block sm:col-span-2">
                        <span class="text-xs font-medium text-slate-500 dark:text-slate-400">Nama Barang</span>
                        <input
                            v-model="form.nama_barang"
                            type="text"
                            required
                            maxlength="150"
                            class="mt-1 w-full rounded-lg border-slate-300 text-sm dark:border-slate-600 dark:bg-slate-700"
                        >
                    </label>

                    <label class="block sm:col-span-2">
                        <span class="text-xs font-medium text-slate-500 dark:text-slate-400">Deskripsi</span>
                        <textarea
                            v-model="form.deskripsi"
                            rows="2"
                            maxlength="1000"
                            class="mt-1 w-full rounded-lg border-slate-300 text-sm dark:border-slate-600 dark:bg-slate-700"
                        />
                    </label>

                    <label class="block">
                        <span class="text-xs font-medium text-slate-500 dark:text-slate-400">Harga Poin</span>
                        <input
                            v-model.number="form.harga_poin"
                            type="number"
                            min="1"
                            required
                            class="mt-1 w-full rounded-lg border-slate-300 text-sm dark:border-slate-600 dark:bg-slate-700"
                        >
                    </label>

                    <label class="block">
                        <span class="text-xs font-medium text-slate-500 dark:text-slate-400">Stok</span>
                        <input
                            v-model.number="form.stok"
                            type="number"
                            min="0"
                            required
                            class="mt-1 w-full rounded-lg border-slate-300 text-sm dark:border-slate-600 dark:bg-slate-700"
                        >
                    </label>

                    <label class="block sm:col-span-2">
                        <span class="text-xs font-medium text-slate-500 dark:text-slate-400">Gambar (opsional, maks 2 MB)</span>
                        <input
                            type="file"
                            accept="image/*"
                            class="mt-1 w-full text-sm text-slate-500 file:mr-3 file:rounded-lg file:border-0 file:bg-primary-50 file:px-3 file:py-1.5 file:text-sm file:font-medium file:text-primary-700 dark:text-slate-400 dark:file:bg-slate-700 dark:file:text-slate-200"
                            @change="gambar = $event.target.files[0] ?? null"
                        >
                    </label>

                    <label class="flex items-center gap-2 sm:col-span-2">
                        <input v-model="form.aktif" type="checkbox" class="rounded border-slate-300 text-teal-600 focus:ring-teal-500">
                        <span class="text-sm text-slate-600 dark:text-slate-300">Tampilkan di katalog siswa</span>
                    </label>
                </div>

                <div class="flex gap-2">
                    <button
                        type="submit"
                        :disabled="saving"
                        class="rounded-lg bg-teal-600 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-teal-700 disabled:opacity-50"
                    >
                        {{ saving ? 'Menyimpan...' : (form.id ? 'Simpan Perubahan' : 'Tambah Reward') }}
                    </button>
                    <button
                        v-if="form.id"
                        type="button"
                        class="rounded-lg px-4 py-2 text-sm font-medium text-slate-600 transition-colors hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-700"
                        @click="resetForm"
                    >
                        Batal
                    </button>
                </div>
            </form>

            <div class="overflow-x-auto rounded-xl bg-white shadow-sm dark:bg-slate-800">
                <table class="w-full min-w-[560px] text-sm">
                    <thead class="border-b border-slate-200 text-left text-xs uppercase text-slate-400 dark:border-slate-700">
                        <tr>
                            <th class="px-4 py-3 font-medium">Nama Barang</th>
                            <th class="px-4 py-3 text-right font-medium">Harga Poin</th>
                            <th class="px-4 py-3 text-right font-medium">Stok</th>
                            <th class="px-4 py-3 font-medium">Status</th>
                            <th class="px-4 py-3 text-right font-medium">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="reward in gamifikasi.rewards"
                            :key="reward.id"
                            class="border-b border-slate-100 last:border-0 dark:border-slate-700/60"
                        >
                            <td class="px-4 py-2.5 font-medium text-slate-700 dark:text-slate-200">{{ reward.nama_barang }}</td>
                            <td class="px-4 py-2.5 text-right tabular-nums text-slate-600 dark:text-slate-300">
                                {{ reward.harga_poin.toLocaleString('id-ID') }}
                            </td>
                            <td class="px-4 py-2.5 text-right tabular-nums text-slate-600 dark:text-slate-300">{{ reward.stok }}</td>
                            <td class="px-4 py-2.5">
                                <span
                                    class="rounded-full px-2 py-0.5 text-xs font-semibold"
                                    :class="reward.aktif
                                        ? 'bg-teal-100 text-teal-700 dark:bg-teal-900/40 dark:text-teal-200'
                                        : 'bg-slate-100 text-slate-500 dark:bg-slate-700 dark:text-slate-400'"
                                >
                                    {{ reward.aktif ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>
                            <td class="px-4 py-2.5">
                                <div class="flex justify-end gap-1">
                                    <button
                                        class="rounded-lg px-2.5 py-1 text-xs font-medium text-primary-700 transition-colors hover:bg-primary-50 dark:text-primary-300 dark:hover:bg-slate-700"
                                        @click="editReward(reward)"
                                    >
                                        Ubah
                                    </button>
                                    <button
                                        class="rounded-lg px-2.5 py-1 text-xs font-medium text-red-600 transition-colors hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/20"
                                        @click="hapusReward(reward)"
                                    >
                                        Hapus
                                    </button>
                                </div>
                            </td>
                        </tr>

                        <tr v-if="gamifikasi.rewards.length === 0">
                            <td colspan="5" class="px-4 py-6 text-center text-slate-400">Belum ada reward di katalog.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </template>
    </div>
</template>
