<script setup>
import { ref, onMounted, watch } from 'vue';
import axios from 'axios';
import { useToastStore } from '../../stores/toast';
import Icon from '../../components/Icon.vue';

const toast = useToastStore();
const kelasList = ref([]);
const kelasId = ref('');
const loading = ref(true);
const exporting = ref(false);
const stat = ref(null);

async function loadKelas() {
    const { data } = await axios.get('/api/kelas');
    kelasList.value = data.data;
}

async function loadStatistik() {
    loading.value = true;
    try {
        const { data } = await axios.get('/api/statistik');
        stat.value = data.data;
    } finally {
        loading.value = false;
    }
}

onMounted(async () => {
    await Promise.all([loadKelas(), loadStatistik()]);
});

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
</script>

<template>
    <div class="space-y-4">
        <h1 class="text-2xl font-semibold text-primary-900 dark:text-white">Laporan Global</h1>

        <div class="flex flex-wrap items-end gap-3 rounded-xl bg-white dark:bg-slate-800 p-4 shadow-sm">
            <div>
                <label class="block text-xs font-medium text-slate-500 dark:text-slate-400">Kelas</label>
                <select v-model="kelasId" class="mt-1 rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-700">
                    <option value="">Semua Kelas</option>
                    <option v-for="k in kelasList" :key="k.id" :value="k.id">{{ k.nama_rombel }}</option>
                </select>
            </div>
            <button
                :disabled="exporting"
                class="rounded-lg bg-teal-600 px-4 py-2 text-sm font-medium text-white hover:bg-teal-700 disabled:opacity-50"
                @click="exportExcel"
            >
                {{ exporting ? 'Mengekspor...' : 'Export Rekap Nilai (Excel)' }}
            </button>
        </div>

        <div v-if="!loading" class="grid gap-4 lg:grid-cols-2">
            <div class="rounded-xl bg-white dark:bg-slate-800 p-5 shadow-sm">
                <h2 class="font-semibold text-primary-900 dark:text-white mb-3">Rata-rata Nilai per Kelas</h2>
                <ul class="space-y-2">
                    <li
                        v-for="k in stat.rata_rata_per_kelas"
                        :key="k.kelas_id"
                        class="flex items-center justify-between rounded-lg bg-slate-50 dark:bg-slate-700/50 px-3 py-2 text-sm"
                    >
                        <span class="text-slate-700 dark:text-slate-200">{{ k.nama_rombel }}</span>
                        <span class="font-semibold text-primary-700 dark:text-primary-300">{{ k.rata_rata }} <span class="text-xs font-normal text-slate-400">({{ k.jumlah_kuis }} kuis)</span></span>
                    </li>
                    <li v-if="!stat.rata_rata_per_kelas.length" class="text-sm text-slate-400">Belum ada data nilai.</li>
                </ul>
            </div>

            <div class="rounded-xl bg-white dark:bg-slate-800 p-5 shadow-sm">
                <h2 class="font-semibold text-primary-900 dark:text-white mb-3">Rata-rata Nilai per Bab</h2>
                <ul class="space-y-2">
                    <li
                        v-for="b in stat.rata_rata_per_bab"
                        :key="b.bab_id"
                        class="flex items-center justify-between rounded-lg bg-slate-50 dark:bg-slate-700/50 px-3 py-2 text-sm"
                    >
                        <span class="text-slate-700 dark:text-slate-200">{{ b.judul }}</span>
                        <span class="font-semibold text-primary-700 dark:text-primary-300">{{ b.rata_rata }} <span class="text-xs font-normal text-slate-400">({{ b.jumlah_kuis }} kuis)</span></span>
                    </li>
                    <li v-if="!stat.rata_rata_per_bab.length" class="text-sm text-slate-400">Belum ada data nilai.</li>
                </ul>
            </div>

            <div class="rounded-xl bg-white dark:bg-slate-800 p-5 shadow-sm">
                <h2 class="font-semibold text-primary-900 dark:text-white mb-3">Progres Penyelesaian Materi</h2>
                <ul class="space-y-2">
                    <li v-for="p in stat.progres_materi" :key="p.kelas_id" class="rounded-lg bg-slate-50 dark:bg-slate-700/50 px-3 py-2 text-sm">
                        <div class="flex items-center justify-between mb-1">
                            <span class="text-slate-700 dark:text-slate-200">{{ p.nama_rombel }}</span>
                            <span class="font-semibold text-teal-600">{{ p.persentase }}%</span>
                        </div>
                        <div class="h-1.5 rounded-full bg-slate-200 dark:bg-slate-600 overflow-hidden">
                            <div class="h-full rounded-full bg-teal-500" :style="{ width: `${p.persentase}%` }" />
                        </div>
                    </li>
                    <li v-if="!stat.progres_materi.length" class="text-sm text-slate-400">Belum ada data kelas.</li>
                </ul>
            </div>

            <div class="rounded-xl bg-white dark:bg-slate-800 p-5 shadow-sm">
                <h2 class="font-semibold text-primary-900 dark:text-white mb-3">Siswa di Bawah KKM</h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="text-slate-500 dark:text-slate-400">
                            <tr>
                                <th class="px-3 py-2 text-left font-medium">Siswa</th>
                                <th class="px-3 py-2 text-left font-medium">Kuis</th>
                                <th class="px-3 py-2 text-left font-medium">Skor</th>
                                <th class="px-3 py-2 text-left font-medium">KKM</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                            <tr v-for="(row, i) in stat.siswa_di_bawah_kkm" :key="i">
                                <td class="px-3 py-2 text-slate-700 dark:text-slate-200">{{ row.nama_siswa }}</td>
                                <td class="px-3 py-2 text-slate-500 dark:text-slate-400">{{ row.kuis }}</td>
                                <td class="px-3 py-2 font-semibold text-red-500">{{ row.skor }}</td>
                                <td class="px-3 py-2 text-slate-500 dark:text-slate-400">{{ row.kkm }}</td>
                            </tr>
                        </tbody>
                    </table>
                    <p v-if="!stat.siswa_di_bawah_kkm.length" class="flex items-center gap-2 text-sm text-slate-400 py-2">
                        <Icon name="check-circle" :size="16" class="text-teal-500" />
                        Semua siswa sudah mencapai KKM.
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>
