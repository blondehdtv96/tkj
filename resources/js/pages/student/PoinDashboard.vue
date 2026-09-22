<script setup>
import { ref, onMounted, computed } from 'vue';
import { useGamifikasiStore } from '../../stores/gamifikasi';
import LevelProgress from '../../components/gamifikasi/LevelProgress.vue';
import BadgeGrid from '../../components/gamifikasi/BadgeGrid.vue';
import StatCard from '../../components/StatCard.vue';
import SkeletonBlock from '../../components/SkeletonBlock.vue';
import Icon from '../../components/Icon.vue';

const gamifikasi = useGamifikasiStore();

const loading = ref(true);
const sumber = ref('');
const halaman = ref(1);

const ringkasan = computed(() => gamifikasi.ringkasan);
const lencanaDiraih = computed(() => gamifikasi.lencanaDiraih.length);
const totalLencana = computed(() => ringkasan.value?.lencana?.length ?? 0);

const sumberFilter = [
    { value: '', label: 'Semua sumber' },
    { value: 'materi', label: 'Materi Selesai' },
    { value: 'kuis', label: 'Kuis' },
    { value: 'bonus_kkm', label: 'Bonus Lulus KKM' },
    { value: 'bonus_tepat_waktu', label: 'Bonus Tepat Waktu' },
    { value: 'bonus_bab', label: 'Bonus Bab Tuntas' },
    { value: 'streak', label: 'Bonus Streak' },
    { value: 'redeem', label: 'Penukaran Reward' },
    { value: 'refund', label: 'Pengembalian Poin' },
    { value: 'penyesuaian', label: 'Penyesuaian' },
];

async function loadRiwayat() {
    await gamifikasi.fetchRiwayat({
        page: halaman.value,
        ...(sumber.value ? { sumber: sumber.value } : {}),
    });
}

function gantiFilter() {
    halaman.value = 1;
    loadRiwayat();
}

function gantiHalaman(nomor) {
    halaman.value = nomor;
    loadRiwayat();
}

function formatTanggal(nilai) {
    return new Date(nilai).toLocaleString('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
}

onMounted(async () => {
    try {
        await Promise.all([gamifikasi.fetchRingkasan(), loadRiwayat()]);
        gamifikasi.sinkronkanPoinAuth();
    } finally {
        loading.value = false;
    }
});
</script>

<template>
    <div class="space-y-6">
        <div class="animate-fade-up">
            <h1 class="text-2xl font-semibold text-primary-900 dark:text-white">Poin & Pencapaian</h1>
            <p class="mt-1 text-slate-500 dark:text-slate-400">
                Kumpulkan poin dari membaca materi dan mengerjakan kuis, lalu tukarkan dengan reward sekolah.
            </p>
        </div>

        <SkeletonBlock v-if="loading" />

        <template v-else>
            <LevelProgress
                class="animate-fade-up"
                :level="ringkasan?.level"
                :total-poin="ringkasan?.total_poin ?? 0"
                :streak-hari="ringkasan?.streak_hari ?? 0"
            />

            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <StatCard
                    class="animate-fade-up"
                    style="animation-delay: 60ms"
                    icon="coin"
                    tone="amber"
                    label="Poin Tersedia"
                    :value="(ringkasan?.total_poin ?? 0).toLocaleString('id-ID')"
                />
                <StatCard
                    class="animate-fade-up"
                    style="animation-delay: 120ms"
                    icon="chart-bar"
                    tone="primary"
                    label="Poin Seumur Hidup"
                    :value="(ringkasan?.poin_seumur_hidup ?? 0).toLocaleString('id-ID')"
                />
                <StatCard
                    class="animate-fade-up"
                    style="animation-delay: 180ms"
                    icon="award"
                    tone="teal"
                    label="Lencana Diraih"
                    :value="`${lencanaDiraih} / ${totalLencana}`"
                />
                <StatCard
                    class="animate-fade-up"
                    style="animation-delay: 240ms"
                    icon="trophy"
                    tone="slate"
                    label="Peringkat Kelas"
                    :value="ringkasan?.peringkat_kelas ? `#${ringkasan.peringkat_kelas.peringkat}` : '-'"
                />
            </div>

            <div
                v-if="ringkasan?.poin_tertahan > 0"
                class="animate-fade-up flex items-start gap-2.5 rounded-xl bg-amber-50 p-4 text-sm text-amber-800 ring-1 ring-amber-200 dark:bg-amber-900/20 dark:text-amber-200 dark:ring-amber-800"
            >
                <Icon name="lock" :size="16" class="mt-0.5 shrink-0" />
                <p>
                    <span class="font-semibold">{{ ringkasan.poin_tertahan.toLocaleString('id-ID') }} poin</span>
                    sedang ditahan untuk penukaran yang menunggu verifikasi. Poin kembali bila pengajuan ditolak.
                </p>
            </div>

            <section class="animate-fade-up space-y-3" style="animation-delay: 300ms">
                <h2 class="flex items-center gap-2 font-semibold text-primary-900 dark:text-white">
                    <Icon name="award" :size="16" class="text-primary-500" />
                    Lencana Pencapaian
                </h2>
                <BadgeGrid :lencana="ringkasan?.lencana ?? []" />
            </section>

            <section class="animate-fade-up rounded-xl bg-white dark:bg-slate-800 p-5 shadow-sm" style="animation-delay: 360ms">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <h2 class="flex items-center gap-2 font-semibold text-primary-900 dark:text-white">
                        <Icon name="clipboard" :size="16" class="text-primary-500" />
                        Riwayat Poin
                    </h2>

                    <select
                        v-model="sumber"
                        class="rounded-lg border-slate-300 text-sm dark:border-slate-600 dark:bg-slate-700"
                        @change="gantiFilter"
                    >
                        <option v-for="item in sumberFilter" :key="item.value" :value="item.value">{{ item.label }}</option>
                    </select>
                </div>

                <SkeletonBlock v-if="gamifikasi.loading" class="mt-4" />

                <ul v-else class="mt-4 space-y-2">
                    <li
                        v-for="log in gamifikasi.riwayat"
                        :key="log.id"
                        class="flex items-center justify-between gap-3 rounded-lg bg-slate-50 px-3 py-2.5 dark:bg-slate-700/50"
                    >
                        <div class="min-w-0">
                            <p class="truncate text-sm font-medium text-slate-700 dark:text-slate-200">{{ log.keterangan }}</p>
                            <p class="mt-0.5 text-xs text-slate-400">
                                {{ log.sumber_label }} &middot; {{ formatTanggal(log.created_at) }}
                            </p>
                        </div>
                        <span
                            class="shrink-0 text-sm font-bold tabular-nums"
                            :class="log.jumlah > 0 ? 'text-teal-600 dark:text-teal-400' : 'text-red-500'"
                        >
                            {{ log.jumlah > 0 ? '+' : '' }}{{ log.jumlah }}
                        </span>
                    </li>

                    <li v-if="gamifikasi.riwayat.length === 0" class="py-4 text-center text-sm text-slate-400">
                        Belum ada perolehan poin. Mulai dari menyelesaikan satu materi.
                    </li>
                </ul>

                <div
                    v-if="gamifikasi.riwayatMeta && gamifikasi.riwayatMeta.last_page > 1"
                    class="mt-4 flex items-center justify-between gap-3 text-sm"
                >
                    <button
                        class="rounded-lg px-3 py-1.5 font-medium text-primary-700 transition-colors hover:bg-primary-50 disabled:opacity-40 dark:text-primary-300 dark:hover:bg-slate-700"
                        :disabled="halaman <= 1"
                        @click="gantiHalaman(halaman - 1)"
                    >
                        Sebelumnya
                    </button>
                    <span class="text-slate-400">
                        Halaman {{ gamifikasi.riwayatMeta.current_page }} dari {{ gamifikasi.riwayatMeta.last_page }}
                    </span>
                    <button
                        class="rounded-lg px-3 py-1.5 font-medium text-primary-700 transition-colors hover:bg-primary-50 disabled:opacity-40 dark:text-primary-300 dark:hover:bg-slate-700"
                        :disabled="halaman >= gamifikasi.riwayatMeta.last_page"
                        @click="gantiHalaman(halaman + 1)"
                    >
                        Berikutnya
                    </button>
                </div>
            </section>
        </template>
    </div>
</template>
