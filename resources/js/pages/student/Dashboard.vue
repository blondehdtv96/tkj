<script setup>
import { ref, onMounted, computed } from 'vue';
import axios from 'axios';
import { useAuthStore } from '../../stores/auth';
import { useMateriStore } from '../../stores/materi';
import { useGamifikasiStore } from '../../stores/gamifikasi';
import SkeletonBlock from '../../components/SkeletonBlock.vue';
import StatCard from '../../components/StatCard.vue';
import LevelProgress from '../../components/gamifikasi/LevelProgress.vue';
import Icon from '../../components/Icon.vue';
import { salamWaktu } from '../../utils/greeting';

const auth = useAuthStore();
const materiStore = useMateriStore();
const gamifikasi = useGamifikasiStore();

const mapelList = ref([]);
const nilaiTerbaru = ref([]);
const totalKuis = ref(0);
const loading = ref(true);

const rataRataTerbaru = computed(() => {
    const skor = nilaiTerbaru.value.map((h) => h.skor).filter((s) => s !== null && s !== undefined);
    if (! skor.length) return '-';
    return (skor.reduce((a, b) => a + b, 0) / skor.length).toFixed(1);
});

onMounted(async () => {
    try {
        const tingkat = auth.user?.kelas?.tingkat;
        const [mapel, nilai] = await Promise.all([
            materiStore.fetchMataPelajaran(tingkat),
            axios.get('/api/hasil-kuis', { params: { sort_by: 'waktu_selesai', sort_dir: 'desc' } }),
            gamifikasi.fetchRingkasan(),
        ]);
        mapelList.value = mapel;
        nilaiTerbaru.value = nilai.data.data.slice(0, 5);
        totalKuis.value = nilai.data.meta?.total ?? nilai.data.data.length;
        gamifikasi.sinkronkanPoinAuth();
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
            <p class="mt-1 text-slate-500 dark:text-slate-400">
                Kelas {{ auth.user?.kelas?.nama_rombel ?? '-' }} &middot; Semangat belajar TKJ hari ini!
            </p>
        </div>

        <SkeletonBlock v-if="loading" />
        <LevelProgress
            v-else
            class="animate-fade-up"
            :level="gamifikasi.ringkasan?.level"
            :total-poin="gamifikasi.ringkasan?.total_poin ?? 0"
            :streak-hari="gamifikasi.ringkasan?.streak_hari ?? 0"
        />

        <div class="grid gap-4 sm:grid-cols-3">
            <StatCard
                class="animate-fade-up"
                style="animation-delay: 60ms"
                icon="book"
                tone="primary"
                label="Mata Pelajaran"
                :value="loading ? '-' : mapelList.length"
            />
            <StatCard
                class="animate-fade-up"
                style="animation-delay: 120ms"
                icon="clipboard"
                tone="teal"
                label="Kuis Dikerjakan"
                :value="loading ? '-' : totalKuis"
            />
            <StatCard
                class="animate-fade-up"
                style="animation-delay: 180ms"
                icon="chart-bar"
                tone="amber"
                label="Rata-rata Kuis"
                :value="loading ? '-' : rataRataTerbaru"
            />
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <div class="animate-fade-up rounded-xl bg-white dark:bg-slate-800 p-5 shadow-sm" style="animation-delay: 240ms">
                <h2 class="mb-3 flex items-center gap-2 font-semibold text-primary-900 dark:text-white">
                    <Icon name="book" :size="16" class="text-primary-500" />
                    Mata Pelajaran
                </h2>
                <SkeletonBlock v-if="loading" />
                <ul v-else class="space-y-2">
                    <li v-for="mapel in mapelList" :key="mapel.id">
                        <RouterLink
                            :to="`/siswa/materi/${mapel.id}`"
                            class="block rounded-lg px-3 py-2 text-sm bg-slate-50 dark:bg-slate-700/50 hover:bg-teal-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 transition-colors"
                        >
                            {{ mapel.nama }}
                        </RouterLink>
                    </li>
                    <li v-if="!loading && mapelList.length === 0" class="text-sm text-slate-400">
                        Belum ada mata pelajaran untuk kelasmu.
                    </li>
                </ul>
            </div>

            <div class="animate-fade-up rounded-xl bg-white dark:bg-slate-800 p-5 shadow-sm" style="animation-delay: 300ms">
                <h2 class="mb-3 flex items-center gap-2 font-semibold text-primary-900 dark:text-white">
                    <Icon name="chart-bar" :size="16" class="text-primary-500" />
                    Nilai Kuis Terbaru
                </h2>
                <SkeletonBlock v-if="loading" />
                <ul v-else class="space-y-2">
                    <li
                        v-for="hasil in nilaiTerbaru"
                        :key="hasil.id"
                        class="flex items-center justify-between rounded-lg px-3 py-2 text-sm bg-slate-50 dark:bg-slate-700/50"
                    >
                        <span class="text-slate-700 dark:text-slate-200">{{ hasil.kuis?.judul }}</span>
                        <span
                            class="font-semibold"
                            :class="hasil.skor >= hasil.kuis?.kkm ? 'text-teal-600' : 'text-red-500'"
                        >
                            {{ hasil.skor ?? '-' }}
                        </span>
                    </li>
                    <li v-if="!loading && nilaiTerbaru.length === 0" class="text-sm text-slate-400">
                        Belum ada kuis yang dikerjakan.
                    </li>
                </ul>
            </div>
        </div>
    </div>
</template>
