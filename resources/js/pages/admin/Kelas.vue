<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { useToastStore } from '../../stores/toast';
import { useConfirmStore } from '../../stores/confirm';

const toast = useToastStore();
const confirmStore = useConfirmStore();
const kelasList = ref([]);
const guruList = ref([]);
const loading = ref(true);
const saving = ref(false);
const error = ref('');

const kosongForm = () => ({ id: null, tingkat: 10, nama_rombel: '', wali_kelas_id: '' });
const form = ref(kosongForm());
const showForm = ref(false);

async function loadGuru() {
    const { data } = await axios.get('/api/users', { params: { role: 'guru' } });
    guruList.value = data.data;
}

async function loadKelas() {
    loading.value = true;
    try {
        const { data } = await axios.get('/api/kelas');
        kelasList.value = data.data;
    } finally {
        loading.value = false;
    }
}

onMounted(async () => {
    await Promise.all([loadGuru(), loadKelas()]);
});

function tambahBaru() {
    form.value = kosongForm();
    error.value = '';
    showForm.value = true;
}

function editKelas(kelas) {
    form.value = {
        id: kelas.id,
        tingkat: kelas.tingkat,
        nama_rombel: kelas.nama_rombel,
        wali_kelas_id: kelas.wali_kelas_id ?? '',
    };
    error.value = '';
    showForm.value = true;
}

async function simpanKelas() {
    saving.value = true;
    error.value = '';
    try {
        const payload = { ...form.value, wali_kelas_id: form.value.wali_kelas_id || null };
        if (form.value.id) {
            await axios.put(`/api/kelas/${form.value.id}`, payload);
        } else {
            await axios.post('/api/kelas', payload);
        }
        showForm.value = false;
        toast.success('Kelas berhasil disimpan.');
        await loadKelas();
    } catch (e) {
        error.value = Object.values(e.response?.data?.errors ?? {}).flat().join(' ') || 'Gagal menyimpan kelas.';
    } finally {
        saving.value = false;
    }
}

async function hapusKelas(id) {
    if (! (await confirmStore.ask('Hapus kelas ini? Siswa di kelas ini tidak akan ikut terhapus.', { title: 'Hapus Kelas' }))) return;
    await axios.delete(`/api/kelas/${id}`);
    toast.success('Kelas berhasil dihapus.');
    await loadKelas();
}
</script>

<template>
    <div class="space-y-4">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h1 class="text-2xl font-semibold text-primary-900 dark:text-white">Kelola Kelas</h1>
            <button class="rounded-lg bg-primary-600 px-4 py-2 text-sm font-medium text-white hover:bg-primary-700" @click="tambahBaru">
                + Kelas Baru
            </button>
        </div>

        <form v-if="showForm" class="space-y-3 rounded-xl bg-white dark:bg-slate-800 p-4 shadow-sm" @submit.prevent="simpanKelas">
            <p v-if="error" class="rounded-lg bg-red-50 dark:bg-red-900/40 px-3 py-2 text-xs text-red-600 dark:text-red-300">{{ error }}</p>
            <div class="grid gap-3 sm:grid-cols-3">
                <select v-model.number="form.tingkat" class="rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-700 text-sm">
                    <option :value="10">Kelas 10</option>
                    <option :value="11">Kelas 11</option>
                    <option :value="12">Kelas 12</option>
                </select>
                <input v-model="form.nama_rombel" required placeholder="Nama rombel (contoh: X TKJ 1)" class="rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-700 text-sm" />
                <select v-model="form.wali_kelas_id" class="rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-700 text-sm">
                    <option value="">Tanpa Wali Kelas</option>
                    <option v-for="g in guruList" :key="g.id" :value="g.id">{{ g.name }}</option>
                </select>
            </div>
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
                        <th class="px-4 py-3 text-left font-medium">Tingkat</th>
                        <th class="px-4 py-3 text-left font-medium">Nama Rombel</th>
                        <th class="px-4 py-3 text-left font-medium">Wali Kelas</th>
                        <th class="px-4 py-3 text-left font-medium">Jumlah Siswa</th>
                        <th class="px-4 py-3 text-left font-medium">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                    <tr v-for="kelas in kelasList" :key="kelas.id">
                        <td class="px-4 py-3 text-slate-700 dark:text-slate-200">{{ kelas.tingkat }}</td>
                        <td class="px-4 py-3 text-slate-700 dark:text-slate-200">{{ kelas.nama_rombel }}</td>
                        <td class="px-4 py-3 text-slate-500 dark:text-slate-400">{{ kelas.wali_kelas?.name ?? '-' }}</td>
                        <td class="px-4 py-3 text-slate-500 dark:text-slate-400">{{ kelas.jumlah_siswa ?? 0 }}</td>
                        <td class="px-4 py-3">
                            <div class="flex gap-2 text-xs">
                                <button class="text-primary-600 hover:underline" @click="editKelas(kelas)">Edit</button>
                                <button class="text-red-500 hover:underline" @click="hapusKelas(kelas.id)">Hapus</button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
            <p v-if="!loading && !kelasList.length" class="px-4 py-6 text-sm text-slate-400">Belum ada kelas.</p>
        </div>
    </div>
</template>
