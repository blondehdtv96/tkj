<script setup>
import { ref, onMounted, computed } from 'vue';
import { useAuthStore } from '../../stores/auth';
import { useGamifikasiStore } from '../../stores/gamifikasi';
import SkeletonBlock from '../../components/SkeletonBlock.vue';
import Icon from '../../components/Icon.vue';

const auth = useAuthStore();
const gamifikasi = useGamifikasiStore();

const periode = ref('mingguan');
const lingkup = ref(auth.role === 'siswa' ? 'kelas' : 'global');
const baris = ref([]);
const meta = ref(null);
const loading = ref(true);

const periodeOptions = [
    { value: 'mingguan', label: 'Mingguan' },
    { value: 'bulanan', label: 'Bulanan' },
    { value: 'semester', label: 'Semester' },
];

const lingkupOptions = [
    { value: 'kelas', label: 'Kelas Saya' },
    { value: 'angkatan', label: 'Angkatan' },
    { value: 'global', label: 'Seluruh Sekolah' },
];

// Guru dan admin tidak terikat satu rombel, jadi opsi "Kelas Saya" disembunyikan.
const lingkupTersedia = computed(() =>
    auth.role === 'siswa' ? lingkupOptions : lingkupOptions.filter((item) => item.value !== 'kelas'),
);

const podium = computed(() => baris.value.slice(0, 3));
const sisanya = computed(() => baris.value.slice(3));

const warnaPodium = ['bg-amber-400 text-amber-950', 'bg-slate-300 text-slate-800', 'bg-orange-400 text-orange-950'];

async function load() {
    loading.value = true;
    try {
        const hasil = await gamifikasi.fetchLeaderboard({ periode: periode.value, lingkup: lingkup.value });
        baris.value = hasil.data;
        meta.value = hasil.meta;
    } finally {
        loading.value = false;
    }
}

onMounted(load);
</script>

<template>
    <div class="space-y-5">
        <div class="animate-fade-up">
            <h1 class="text-2xl font-semibold text-primary-900 dark:text-white">Papan Peringkat</h1>
            <p class="mt-1 text-slate-500 dark:text-slate-400">
                Peringkat dihitung dari poin yang diperoleh pada periode berjalan, bukan total saldo.
            </p>
        </div>

        <div class="flex flex-wrap gap-3 rounded-xl bg-white p-4 shadow-sm dark:bg-slate-800">
            <div>
                <label class="block text-xs font-medium text-slate-500 dark:text-slate-400">Periode</label>
                <select
                    v-model="periode"
                    class="mt-1 rounded-lg border-slate-300 text-sm dark:border-slate-600 dark:bg-slate-700"
                    @change="load"
                >
                    <option v-for="item in periodeOptions" :key="item.value" :value="item.value">{{ item.label }}</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-medium text-slate-500 dark:text-slate-400">Lingkup</label>
                <select
                    v-model="lingkup"
                    class="mt-1 rounded-lg border-slate-300 text-sm dark:border-slate-600 dark:bg-slate-700"
                    @change="load"
                >
                    <option v-for="item in lingkupTersedia" :key="item.value" :value="item.value">{{ item.label }}</option>
                </select>
            </div>

            <p v-if="meta" class="w-full text-xs text-slate-400 sm:w-auto sm:self-end sm:pb-2">
                Dihitung sejak {{ new Date(meta.mulai).toLocaleDateString('id-ID', { day: '2-digit', month: 'long', year: 'numeric' }) }}
            </p>
        </div>

        <SkeletonBlock v-if="loading" />

        <template v-else>
            <div v-if="podium.length" class="grid gap-3 sm:grid-cols-3">
                <div
                    v-for="(item, index) in podium"
                    :key="item.user_id"
                    class="animate-fade-up flex items-center gap-3 rounded-xl bg-white p-4 shadow-sm dark:bg-slate-800"
                    :class="item.user_id === auth.user?.id ? 'ring-2 ring-teal-400' : ''"
                    :style="{ animationDelay: `${index * 60}ms` }"
                >
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full font-bold" :class="warnaPodium[index]">
                        {{ item.peringkat }}
                    </div>
                    <div class="min-w-0">
                        <p class="truncate font-semibold text-primary-900 dark:text-white">{{ item.nama }}</p>
                        <p class="text-xs text-slate-400">{{ item.kelas ?? 'Tanpa kelas' }} &middot; {{ item.level }}</p>
                        <p class="mt-0.5 inline-flex items-center gap-1 text-sm font-bold tabular-nums text-amber-600 dark:text-amber-400">
                            <Icon name="coin" :size="14" />
                            {{ item.poin.toLocaleString('id-ID') }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto rounded-xl bg-white shadow-sm dark:bg-slate-800">
                <table class="w-full min-w-[480px] text-sm">
                    <thead class="border-b border-slate-200 text-left text-xs uppercase text-slate-400 dark:border-slate-700">
                        <tr>
                            <th class="px-4 py-3 font-medium">#</th>
                            <th class="px-4 py-3 font-medium">Nama</th>
                            <th class="px-4 py-3 font-medium">Kelas</th>
                            <th class="px-4 py-3 font-medium">Level</th>
                            <th class="px-4 py-3 text-right font-medium">Poin</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="item in sisanya"
                            :key="item.user_id"
                            class="border-b border-slate-100 last:border-0 dark:border-slate-700/60"
                            :class="item.user_id === auth.user?.id ? 'bg-teal-50 dark:bg-teal-900/20' : ''"
                        >
                            <td class="px-4 py-2.5 tabular-nums text-slate-400">{{ item.peringkat }}</td>
                            <td class="px-4 py-2.5 font-medium text-slate-700 dark:text-slate-200">{{ item.nama }}</td>
                            <td class="px-4 py-2.5 text-slate-500 dark:text-slate-400">{{ item.kelas ?? '-' }}</td>
                            <td class="px-4 py-2.5 text-slate-500 dark:text-slate-400">{{ item.level }}</td>
                            <td class="px-4 py-2.5 text-right font-semibold tabular-nums text-primary-900 dark:text-white">
                                {{ item.poin.toLocaleString('id-ID') }}
                            </td>
                        </tr>

                        <tr v-if="baris.length === 0">
                            <td colspan="5" class="px-4 py-6 text-center text-slate-400">
                                Belum ada perolehan poin pada periode ini.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div
                v-if="auth.role === 'siswa'"
                class="flex items-center gap-2.5 rounded-xl bg-white p-4 text-sm shadow-sm dark:bg-slate-800"
            >
                <Icon name="trophy" :size="18" class="shrink-0 text-amber-500" />
                <p class="text-slate-600 dark:text-slate-300">
                    <template v-if="meta?.posisi_saya">
                        Posisimu peringkat <span class="font-semibold">#{{ meta.posisi_saya.peringkat }}</span>
                        dengan {{ meta.posisi_saya.poin.toLocaleString('id-ID') }} poin pada periode ini.
                    </template>
                    <template v-else>
                        Kamu belum masuk papan peringkat periode ini. Selesaikan materi atau kuis untuk mulai mengumpulkan poin.
                    </template>
                </p>
            </div>
        </template>
    </div>
</template>
