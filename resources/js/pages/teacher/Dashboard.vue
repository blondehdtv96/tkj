<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { useAuthStore } from '../../stores/auth';
import SkeletonBlock from '../../components/SkeletonBlock.vue';
import StatCard from '../../components/StatCard.vue';
import Icon from '../../components/Icon.vue';
import { salamWaktu } from '../../utils/greeting';

const auth = useAuthStore();
const loading = ref(true);
const stat = ref(null);

onMounted(async () => {
    try {
        const { data } = await axios.get('/api/statistik');
        stat.value = data.data;
    } finally {
        loading.value = false;
    }
});
</script>

<template>
    <div class="space-y-6">
        <div class="animate-fade-up">
            <p class="text-sm font-medium text-teal-600 dark:text-teal-400">{{ salamWaktu() }}</p>
            <h1 class="text-2xl font-semibold text-primary-900 dark:text-white">{{ auth.user?.name }}</h1>
            <p class="mt-1 text-slate-500 dark:text-slate-400">Ringkasan kelas, bab, dan nilai siswa.</p>
        </div>

        <div v-if="loading" class="grid gap-4 sm:grid-cols-3">
            <div class="rounded-xl bg-white dark:bg-slate-800 p-5 shadow-sm"><SkeletonBlock :rows="2" /></div>
            <div class="rounded-xl bg-white dark:bg-slate-800 p-5 shadow-sm"><SkeletonBlock :rows="2" /></div>
            <div class="rounded-xl bg-white dark:bg-slate-800 p-5 shadow-sm"><SkeletonBlock :rows="2" /></div>
        </div>

        <div v-else class="grid gap-4 sm:grid-cols-3">
            <StatCard
                class="animate-fade-up"
                style="animation-delay: 60ms"
                icon="graduation-cap"
                tone="primary"
                label="Kelas Aktif"
                :value="stat.rata_rata_per_kelas.length"
            />
            <StatCard
                class="animate-fade-up"
                style="animation-delay: 120ms"
                icon="book"
                tone="teal"
                label="Bab dengan Kuis"
                :value="stat.rata_rata_per_bab.length"
            />
            <StatCard
                class="animate-fade-up"
                style="animation-delay: 180ms"
                icon="chart-bar"
                :tone="stat.siswa_di_bawah_kkm.length ? 'red' : 'teal'"
                label="Siswa di Bawah KKM"
                :value="stat.siswa_di_bawah_kkm.length"
            />
        </div>

        <div v-if="loading" class="grid gap-4 lg:grid-cols-2">
            <div class="rounded-xl bg-white dark:bg-slate-800 p-5 shadow-sm"><SkeletonBlock /></div>
            <div class="rounded-xl bg-white dark:bg-slate-800 p-5 shadow-sm"><SkeletonBlock /></div>
        </div>

        <div v-else class="grid gap-4 lg:grid-cols-2">
            <div class="animate-fade-up rounded-xl bg-white dark:bg-slate-800 p-5 shadow-sm" style="animation-delay: 240ms">
                <h2 class="mb-3 flex items-center gap-2 font-semibold text-primary-900 dark:text-white">
                    <Icon name="graduation-cap" :size="16" class="text-primary-500" />
                    Rata-rata Nilai per Kelas
                </h2>
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

            <div class="animate-fade-up rounded-xl bg-white dark:bg-slate-800 p-5 shadow-sm" style="animation-delay: 300ms">
                <h2 class="mb-3 flex items-center gap-2 font-semibold text-primary-900 dark:text-white">
                    <Icon name="book" :size="16" class="text-primary-500" />
                    Rata-rata Nilai per Bab
                </h2>
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

            <div class="animate-fade-up rounded-xl bg-white dark:bg-slate-800 p-5 shadow-sm lg:col-span-2" style="animation-delay: 360ms">
                <h2 class="mb-3 flex items-center gap-2 font-semibold text-primary-900 dark:text-white">
                    <Icon name="clipboard" :size="16" class="text-primary-500" />
                    Siswa di Bawah KKM
                </h2>
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
