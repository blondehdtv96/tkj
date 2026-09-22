<script setup>
import { ref, onMounted, watch } from 'vue';
import axios from 'axios';
import { useToastStore } from '../../stores/toast';
import { useConfirmStore } from '../../stores/confirm';

const toast = useToastStore();
const confirmStore = useConfirmStore();
const tingkatList = [10, 11, 12];
const tingkat = ref(10);
const mapelList = ref([]);
const loading = ref(true);
const saving = ref(false);
const error = ref('');

const kosongForm = () => ({ id: null, nama: '', tingkat: 10, deskripsi: '' });
const form = ref(kosongForm());
const showForm = ref(false);

async function loadMapel() {
    loading.value = true;
    try {
        const { data } = await axios.get('/api/mata-pelajaran', { params: { tingkat: tingkat.value } });
        mapelList.value = data.data;
    } finally {
        loading.value = false;
    }
}

onMounted(loadMapel);
watch(tingkat, loadMapel);

function tambahBaru() {
    form.value = { ...kosongForm(), tingkat: tingkat.value };
    error.value = '';
    showForm.value = true;
}

function editMapel(mapel) {
    form.value = { id: mapel.id, nama: mapel.nama, tingkat: mapel.tingkat, deskripsi: mapel.deskripsi ?? '' };
    error.value = '';
    showForm.value = true;
}

async function simpanMapel() {
    saving.value = true;
    error.value = '';
    try {
        if (form.value.id) {
            await axios.put(`/api/mata-pelajaran/${form.value.id}`, form.value);
        } else {
            await axios.post('/api/mata-pelajaran', form.value);
        }
        showForm.value = false;
        toast.success('Mata pelajaran berhasil disimpan.');
        await loadMapel();
    } catch (e) {
        error.value = Object.values(e.response?.data?.errors ?? {}).flat().join(' ') || 'Gagal menyimpan mata pelajaran.';
    } finally {
        saving.value = false;
    }
}

async function hapusMapel(id) {
    if (! (await confirmStore.ask('Hapus mata pelajaran ini beserta seluruh bab, materi, dan kuis di dalamnya?', { title: 'Hapus Mata Pelajaran' }))) return;
    await axios.delete(`/api/mata-pelajaran/${id}`);
    toast.success('Mata pelajaran berhasil dihapus.');
    await loadMapel();
}
</script>

<template>
    <div class="space-y-4">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h1 class="text-2xl font-semibold text-primary-900 dark:text-white">Kelola Mata Pelajaran</h1>
            <button class="rounded-lg bg-primary-600 px-4 py-2 text-sm font-medium text-white hover:bg-primary-700" @click="tambahBaru">
                + Mata Pelajaran Baru
            </button>
        </div>

        <div class="flex flex-wrap gap-3 items-end rounded-xl bg-white dark:bg-slate-800 p-4 shadow-sm">
            <div>
                <label class="block text-xs font-medium text-slate-500 dark:text-slate-400">Tingkat</label>
                <select v-model.number="tingkat" class="mt-1 rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-700">
                    <option v-for="t in tingkatList" :key="t" :value="t">Kelas {{ t }}</option>
                </select>
            </div>
        </div>

        <form v-if="showForm" class="space-y-3 rounded-xl bg-white dark:bg-slate-800 p-4 shadow-sm" @submit.prevent="simpanMapel">
            <p v-if="error" class="rounded-lg bg-red-50 dark:bg-red-900/40 px-3 py-2 text-xs text-red-600 dark:text-red-300">{{ error }}</p>
            <div class="grid gap-3 sm:grid-cols-3">
                <input v-model="form.nama" required placeholder="Nama mata pelajaran" class="rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-700 text-sm sm:col-span-2" />
                <select v-model.number="form.tingkat" class="rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-700 text-sm">
                    <option v-for="t in tingkatList" :key="t" :value="t">Kelas {{ t }}</option>
                </select>
            </div>
            <textarea v-model="form.deskripsi" rows="2" placeholder="Deskripsi (opsional)" class="w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-700 text-sm" />
            <div class="flex gap-2">
                <button type="submit" :disabled="saving" class="rounded-lg bg-primary-600 px-4 py-2 text-sm text-white disabled:opacity-50">
                    {{ saving ? 'Menyimpan...' : 'Simpan' }}
                </button>
                <button type="button" class="rounded-lg bg-slate-100 dark:bg-slate-700 px-4 py-2 text-sm text-slate-600 dark:text-slate-300" @click="showForm = false">
                    Batal
                </button>
            </div>
        </form>

        <div class="overflow-x-auto rounded-xl bg-white dark:bg-slate-800 shadow-sm">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50 dark:bg-slate-700/50 text-slate-500 dark:text-slate-300">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium">Nama</th>
                        <th class="px-4 py-3 text-left font-medium">Deskripsi</th>
                        <th class="px-4 py-3 text-left font-medium">Jumlah Bab</th>
                        <th class="px-4 py-3 text-left font-medium">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                    <tr v-for="mapel in mapelList" :key="mapel.id">
                        <td class="px-4 py-3 text-slate-700 dark:text-slate-200">{{ mapel.nama }}</td>
                        <td class="px-4 py-3 text-slate-500 dark:text-slate-400">{{ mapel.deskripsi ?? '-' }}</td>
                        <td class="px-4 py-3 text-slate-500 dark:text-slate-400">{{ mapel.jumlah_bab ?? 0 }}</td>
                        <td class="px-4 py-3">
                            <div class="flex gap-2 text-xs">
                                <button class="text-primary-600 hover:underline" @click="editMapel(mapel)">Edit</button>
                                <button class="text-red-500 hover:underline" @click="hapusMapel(mapel.id)">Hapus</button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
            <p v-if="!loading && !mapelList.length" class="px-4 py-6 text-sm text-slate-400">Belum ada mata pelajaran untuk tingkat ini.</p>
        </div>
    </div>
</template>
