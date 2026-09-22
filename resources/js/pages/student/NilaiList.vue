<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const hasilList = ref([]);
const loading = ref(true);

onMounted(async () => {
    try {
        const { data } = await axios.get('/api/hasil-kuis', {
            params: { sort_by: 'waktu_selesai', sort_dir: 'desc' },
        });
        hasilList.value = data.data;
    } finally {
        loading.value = false;
    }
});
</script>

<template>
    <div class="space-y-4">
        <h1 class="text-2xl font-semibold text-primary-900 dark:text-white">Nilai Kuis Saya</h1>

        <p v-if="!loading && hasilList.length === 0" class="text-slate-400 text-sm">Belum ada nilai kuis.</p>

        <div class="overflow-x-auto rounded-xl bg-white dark:bg-slate-800 shadow-sm">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50 dark:bg-slate-700/50 text-slate-500 dark:text-slate-300">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium">Kuis</th>
                        <th class="px-4 py-3 text-left font-medium">Bab</th>
                        <th class="px-4 py-3 text-left font-medium">Skor</th>
                        <th class="px-4 py-3 text-left font-medium">Status</th>
                        <th class="px-4 py-3 text-left font-medium">Waktu Selesai</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                    <tr v-for="hasil in hasilList" :key="hasil.id">
                        <td class="px-4 py-3">
                            <RouterLink :to="`/siswa/kuis/hasil/${hasil.id}`" class="text-primary-700 dark:text-primary-300 hover:underline">
                                {{ hasil.kuis?.judul }}
                            </RouterLink>
                        </td>
                        <td class="px-4 py-3 text-slate-500 dark:text-slate-400">{{ hasil.kuis?.bab_judul }}</td>
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
                            {{ new Date(hasil.waktu_selesai).toLocaleString('id-ID') }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>
