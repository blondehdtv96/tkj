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
const totalSiswa = ref(0);
const totalGuru = ref(0);
const totalKelas = ref(0);
const totalMapel = ref(0);
const stat = ref(null);

onMounted(async () => {
    try {
        const [siswa, guru, kelas, mapel, statistik] = await Promise.all([
            axios.get('/api/users', { params: { role: 'siswa' } }),
            axios.get('/api/users', { params: { role: 'guru' } }),
            axios.get('/api/kelas'),
            axios.get('/api/mata-pelajaran'),
            axios.get('/api/statistik'),
        ]);
        totalSiswa.value = siswa.data.meta?.total ?? siswa.data.data.length;
        totalGuru.value = guru.data.meta?.total ?? guru.data.data.length;
        totalKelas.value = kelas.data.data.length;
        totalMapel.value = mapel.data.data.length;
        stat.value = statistik.data.data;
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
            <p class="mt-1 text-slate-500 dark:text-slate-400">Statistik global pengguna, kelas, dan mata pelajaran.</p>
        </div>

        <div v-if="loading" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div v-for="i in 4" :key="i" class="rounded-xl bg-white dark:bg-slate-800 p-5 shadow-sm"><SkeletonBlock :rows="2" /></div>
        </div>

        <div v-else class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <StatCard class="animate-fade-up" style="animation-delay: 60ms" icon="users" tone="primary" label="Siswa" :value="totalSiswa" />
            <StatCard class="animate-fade-up" style="animation-delay: 120ms" icon="user" tone="teal" label="Guru" :value="totalGuru" />
            <StatCard class="animate-fade-up" style="animation-delay: 180ms" icon="graduation-cap" tone="amber" label="Kelas" :value="totalKelas" />
            <StatCard class="animate-fade-up" style="animation-delay: 240ms" icon="book" tone="slate" label="Mata Pelajaran" :value="totalMapel" />
        </div>

        <div v-if="loading" class="grid gap-4 lg:grid-cols-2">
            <div class="rounded-xl bg-white dark:bg-slate-800 p-5 shadow-sm"><SkeletonBlock /></div>
            <div class="rounded-xl bg-white dark:bg-slate-800 p-5 shadow-sm"><SkeletonBlock /></div>
        </div>

        <div v-else class="grid gap-4 lg:grid-cols-2">
            <div class="animate-fade-up rounded-xl bg-white dark:bg-slate-800 p-5 shadow-sm" style="animation-delay: 300ms">
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
                        <span class="font-semibold text-primary-700 dark:text-primary-300">{{ k.rata_rata }}</span>
                    </li>
                    <li v-if="!stat.rata_rata_per_kelas.length" class="text-sm text-slate-400">Belum ada data nilai.</li>
                </ul>
            </div>

            <div class="animate-fade-up rounded-xl bg-white dark:bg-slate-800 p-5 shadow-sm" style="animation-delay: 360ms">
                <h2 class="mb-3 flex items-center gap-2 font-semibold text-primary-900 dark:text-white">
                    <Icon name="chart-bar" :size="16" class="text-primary-500" />
                    Progres Penyelesaian Materi
                </h2>
                <ul class="space-y-2">
                    <li
                        v-for="p in stat.progres_materi"
                        :key="p.kelas_id"
                        class="rounded-lg bg-slate-50 dark:bg-slate-700/50 px-3 py-2 text-sm"
                    >
                        <div class="flex items-center justify-between mb-1">
                            <span class="text-slate-700 dark:text-slate-200">{{ p.nama_rombel }}</span>
                            <span class="font-semibold text-teal-600">{{ p.persentase }}%</span>
                        </div>
                        <div class="h-1.5 rounded-full bg-slate-200 dark:bg-slate-600 overflow-hidden">
                            <div class="h-full rounded-full bg-teal-500 transition-all duration-700" :style="{ width: `${p.persentase}%` }" />
                        </div>
                    </li>
                    <li v-if="!stat.progres_materi.length" class="text-sm text-slate-400">Belum ada data kelas.</li>
                </ul>
            </div>
        </div>
    </div>
</template>
