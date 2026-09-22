<script setup>
import { ref, onMounted, watch } from 'vue';
import axios from 'axios';
import { useToastStore } from '../../stores/toast';
import Icon from '../../components/Icon.vue';

const toast = useToastStore();
const kelasList = ref([]);
const kelasId = ref('');
const diBawahKkm = ref(false);
const sortBy = ref('waktu_selesai');
const sortDir = ref('desc');

const hasilList = ref([]);
const loading = ref(true);
const exporting = ref(false);

async function loadKelas() {
    const { data } = await axios.get('/api/kelas');
    kelasList.value = data.data;
}

async function loadNilai() {
    loading.value = true;
    try {
        const { data } = await axios.get('/api/hasil-kuis', {
            params: {
                kelas_id: kelasId.value || undefined,
                di_bawah_kkm: diBawahKkm.value ? 1 : undefined,
                sort_by: sortBy.value,
                sort_dir: sortDir.value,
            },
        });
        hasilList.value = data.data;
    } finally {
        loading.value = false;
    }
}

function toggleSort(field) {
    if (sortBy.value === field) {
        sortDir.value = sortDir.value === 'asc' ? 'desc' : 'asc';
    } else {
        sortBy.value = field;
        sortDir.value = 'desc';
    }
}

async function exportExcel() {
    exporting.value = true;
    try {
        const response = await axios.get('/api/laporan/export-nilai', {
            params: { kelas_id: kelasId.value || undefined },
            responseType: 'blob',
        });
        const url = window.URL.createObjectURL(new Blob([response.data]));
        const link = document.createElement('a');
        link.href = url;
        link.setAttribute('download', `rekap-nilai-${Date.now()}.xlsx`);
        document.body.appendChild(link);
        link.click();
        link.remove();
        window.URL.revokeObjectURL(url);
        toast.success('Rekap nilai berhasil diunduh.');
    } finally {
        exporting.value = false;
    }
}

onMounted(async () => {
    await loadKelas();
    await loadNilai();
});
watch([kelasId, diBawahKkm, sortBy, sortDir], loadNilai);
</script>

<template>
    <div class="space-y-4">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h1 class="text-2xl font-semibold text-primary-900 dark:text-white">Nilai Siswa</h1>
            <button
                :disabled="exporting"
                class="rounded-lg bg-teal-600 px-4 py-2 text-sm font-medium text-white hover:bg-teal-700 disabled:opacity-50"
                @click="exportExcel"
            >
                {{ exporting ? 'Mengekspor...' : 'Export Excel' }}
            </button>
        </div>

        <div class="flex flex-wrap gap-3 items-end rounded-xl bg-white dark:bg-slate-800 p-4 shadow-sm">
            <div>
                <label class="block text-xs font-medium text-slate-500 dark:text-slate-400">Kelas</label>
                <select v-model="kelasId" class="mt-1 rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-700">
                    <option value="">Semua Kelas</option>
                    <option v-for="k in kelasList" :key="k.id" :value="k.id">{{ k.nama_rombel }}</option>
                </select>
            </div>

            <label class="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-300 pb-2">
                <input v-model="diBawahKkm" type="checkbox" /> Hanya di bawah KKM
            </label>
        </div>

        <div class="overflow-x-auto rounded-xl bg-white dark:bg-slate-800 shadow-sm">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50 dark:bg-slate-700/50 text-slate-500 dark:text-slate-300">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium">Siswa</th>
                        <th class="px-4 py-3 text-left font-medium">Kelas</th>
                        <th class="px-4 py-3 text-left font-medium">Kuis</th>
                        <th class="px-4 py-3 text-left font-medium cursor-pointer select-none" @click="toggleSort('skor')">
                            <span class="inline-flex items-center gap-1">
                                Skor
                                <Icon v-if="sortBy === 'skor'" :name="sortDir === 'asc' ? 'chevron-up' : 'chevron-down'" :size="14" />
                            </span>
                        </th>
                        <th class="px-4 py-3 text-left font-medium">Status</th>
                        <th class="px-4 py-3 text-left font-medium cursor-pointer select-none" @click="toggleSort('waktu_selesai')">
                            <span class="inline-flex items-center gap-1">
                                Waktu Selesai
                                <Icon v-if="sortBy === 'waktu_selesai'" :name="sortDir === 'asc' ? 'chevron-up' : 'chevron-down'" :size="14" />
                            </span>
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                    <tr v-for="hasil in hasilList" :key="hasil.id">
                        <td class="px-4 py-3 text-slate-700 dark:text-slate-200">{{ hasil.user?.name }}</td>
                        <td class="px-4 py-3 text-slate-500 dark:text-slate-400">{{ hasil.user?.kelas?.nama_rombel ?? '-' }}</td>
                        <td class="px-4 py-3 text-slate-500 dark:text-slate-400">{{ hasil.kuis?.judul }}</td>
                        <td class="px-4 py-3 font-semibold">{{ hasil.skor ?? '-' }}</td>
                        <td class="px-4 py-3">
                            <span
                                class="rounded-full px-2 py-0.5 text-xs font-semibold"
                                :class="hasil.skor >= hasil.kuis?.kkm ? 'bg-teal-100 text-teal-700 dark:bg-teal-900 dark:text-teal-200' : 'bg-red-100 text-red-700 dark:bg-red-900 dark:text-red-200'"
                            >
                                {{ hasil.skor >= hasil.kuis?.kkm ? 'Lulus' : 'Belum Lulus' }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-slate-500 dark:text-slate-400">
                            {{ hasil.waktu_selesai ? new Date(hasil.waktu_selesai).toLocaleString('id-ID') : '-' }}
                        </td>
                    </tr>
                </tbody>
            </table>
            <p v-if="!loading && !hasilList.length" class="px-4 py-6 text-sm text-slate-400">Belum ada data nilai untuk filter ini.</p>
        </div>
    </div>
</template>
