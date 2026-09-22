<script setup>
import { ref, onMounted, watch, computed } from 'vue';
import axios from 'axios';
import { useToastStore } from '../../stores/toast';
import SkeletonBlock from '../../components/SkeletonBlock.vue';
import StatCard from '../../components/StatCard.vue';
import Icon from '../../components/Icon.vue';

const toast = useToastStore();

const tingkatList = [10, 11, 12];
const tingkat = ref(10);
const mapelList = ref([]);
const mapelId = ref(null);
const babList = ref([]);
const babId = ref(null);

const konten = ref(null);
const loading = ref(false);
const saving = ref(false);

const labelTipe = {
    pilihan_ganda: 'Pilihan Ganda',
    benar_salah: 'Benar / Salah',
    essay: 'Essay',
    troubleshooting: 'Troubleshooting',
};

const totalPoinMateri = computed(
    () => (konten.value?.materi ?? []).reduce((total, m) => total + poinEfektif(m.poin, konten.value.bawaan.materi), 0),
);

const totalPoinKuis = computed(() =>
    (konten.value?.kuis ?? []).reduce(
        (total, kuis) => total + kuis.soal.reduce((sub, s) => sub + poinEfektif(s.poin, konten.value.bawaan.soal), 0),
        0,
    ),
);

function poinEfektif(poin, bawaan) {
    return poin === null || poin === '' ? bawaan : Number(poin);
}

async function loadMapel() {
    const { data } = await axios.get('/api/mata-pelajaran', { params: { tingkat: tingkat.value } });
    mapelList.value = data.data;
    mapelId.value = mapelList.value[0]?.id ?? null;
}

async function loadBab() {
    babList.value = [];
    babId.value = null;
    if (! mapelId.value) return;
    const { data } = await axios.get('/api/bab', { params: { mata_pelajaran_id: mapelId.value } });
    babList.value = data.data;
    babId.value = babList.value[0]?.id ?? null;
}

async function loadKonten() {
    konten.value = null;
    if (! babId.value) return;

    loading.value = true;
    try {
        const { data } = await axios.get('/api/poin-konten', { params: { bab_id: babId.value } });
        konten.value = data.data;
    } finally {
        loading.value = false;
    }
}

/**
 * Membagi rata poin maksimal kuis ke seluruh soalnya, sebagai titik awal
 * sebelum admin menyesuaikan tiap soal.
 */
function bagiRata(kuis) {
    if (! kuis.soal.length) return;
    const per = Math.max(1, Math.round(konten.value.bawaan.kuis_maksimal / kuis.soal.length));
    kuis.soal.forEach((soal) => { soal.poin = per; });
}

function kosongkanKuis(kuis) {
    kuis.soal.forEach((soal) => { soal.poin = null; });
}

async function simpan() {
    saving.value = true;
    try {
        await axios.post('/api/poin-konten', {
            materi: konten.value.materi.map((m) => ({ id: m.id, poin: m.poin === '' ? null : m.poin })),
            soal: konten.value.kuis.flatMap((k) => k.soal.map((s) => ({ id: s.id, poin: s.poin === '' ? null : s.poin }))),
        });
        toast.success('Pengaturan poin disimpan.');
        await loadKonten();
    } finally {
        saving.value = false;
    }
}

onMounted(loadMapel);
watch(tingkat, loadMapel);
watch(mapelId, loadBab);
watch(babId, loadKonten);
</script>

<template>
    <div class="space-y-5">
        <div class="animate-fade-up">
            <h1 class="text-2xl font-semibold text-primary-900 dark:text-white">Pengaturan Poin</h1>
            <p class="mt-1 text-slate-500 dark:text-slate-400">
                Tentukan poin gamifikasi tiap materi dan tiap soal. Kosongkan kolom poin untuk memakai nilai bawaan.
            </p>
        </div>

        <div class="flex flex-wrap gap-3 rounded-xl bg-white p-4 shadow-sm dark:bg-slate-800">
            <div>
                <label class="block text-xs font-medium text-slate-500 dark:text-slate-400">Tingkat</label>
                <select v-model.number="tingkat" class="mt-1 rounded-lg border-slate-300 text-sm dark:border-slate-600 dark:bg-slate-700">
                    <option v-for="t in tingkatList" :key="t" :value="t">Kelas {{ t }}</option>
                </select>
            </div>

            <div class="min-w-[200px] flex-1">
                <label class="block text-xs font-medium text-slate-500 dark:text-slate-400">Mata Pelajaran</label>
                <select v-model.number="mapelId" class="mt-1 w-full rounded-lg border-slate-300 text-sm dark:border-slate-600 dark:bg-slate-700">
                    <option v-for="m in mapelList" :key="m.id" :value="m.id">{{ m.nama }}</option>
                </select>
            </div>

            <div class="min-w-[200px] flex-1">
                <label class="block text-xs font-medium text-slate-500 dark:text-slate-400">Bab</label>
                <select v-model.number="babId" class="mt-1 w-full rounded-lg border-slate-300 text-sm dark:border-slate-600 dark:bg-slate-700">
                    <option v-for="b in babList" :key="b.id" :value="b.id">{{ b.urutan }}. {{ b.judul }}</option>
                </select>
            </div>
        </div>

        <SkeletonBlock v-if="loading" />

        <template v-else-if="konten">
            <div class="grid gap-4 sm:grid-cols-3">
                <StatCard icon="book" tone="primary" label="Poin Seluruh Materi" :value="totalPoinMateri" />
                <StatCard icon="clipboard" tone="teal" label="Poin Seluruh Soal" :value="totalPoinKuis" />
                <StatCard icon="coin" tone="amber" label="Total Poin Bab" :value="totalPoinMateri + totalPoinKuis" />
            </div>

            <!-- Poin materi -->
            <section class="rounded-xl bg-white p-5 shadow-sm dark:bg-slate-800">
                <h2 class="flex items-center gap-2 font-semibold text-primary-900 dark:text-white">
                    <Icon name="book" :size="16" class="text-primary-500" />
                    Poin Materi
                </h2>

                <div class="mt-3 overflow-x-auto">
                    <table class="w-full min-w-[480px] text-sm">
                        <thead class="border-b border-slate-200 text-left text-xs uppercase text-slate-400 dark:border-slate-700">
                            <tr>
                                <th class="px-2 py-2 font-medium">Materi</th>
                                <th class="px-2 py-2 font-medium">Tipe</th>
                                <th class="px-2 py-2 text-right font-medium">Poin</th>
                                <th class="px-2 py-2 text-right font-medium">Berlaku</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="materi in konten.materi" :key="materi.id" class="border-b border-slate-100 last:border-0 dark:border-slate-700/60">
                                <td class="px-2 py-2 font-medium text-slate-700 dark:text-slate-200">{{ materi.judul }}</td>
                                <td class="px-2 py-2 text-slate-500 dark:text-slate-400">{{ materi.tipe }}</td>
                                <td class="px-2 py-2 text-right">
                                    <input
                                        v-model.number="materi.poin"
                                        type="number"
                                        min="0"
                                        max="1000"
                                        placeholder="bawaan"
                                        class="w-28 rounded-lg border-slate-300 text-right text-sm dark:border-slate-600 dark:bg-slate-700"
                                    >
                                </td>
                                <td class="px-2 py-2 text-right tabular-nums text-slate-500 dark:text-slate-400">
                                    {{ poinEfektif(materi.poin, konten.bawaan.materi) }}
                                </td>
                            </tr>
                            <tr v-if="!konten.materi.length">
                                <td colspan="4" class="px-2 py-4 text-center text-slate-400">Belum ada materi pada bab ini.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>

            <!-- Poin soal per kuis -->
            <section v-for="kuis in konten.kuis" :key="kuis.id" class="rounded-xl bg-white p-5 shadow-sm dark:bg-slate-800">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <h2 class="flex items-center gap-2 font-semibold text-primary-900 dark:text-white">
                        <Icon name="clipboard" :size="16" class="text-primary-500" />
                        {{ kuis.judul }}
                    </h2>
                    <div class="flex gap-2 text-xs">
                        <button class="rounded-lg px-2.5 py-1 font-medium text-primary-700 hover:bg-primary-50 dark:text-primary-300 dark:hover:bg-slate-700" @click="bagiRata(kuis)">
                            Bagi rata {{ konten.bawaan.kuis_maksimal }} poin
                        </button>
                        <button class="rounded-lg px-2.5 py-1 font-medium text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-700" @click="kosongkanKuis(kuis)">
                            Kembalikan ke bawaan
                        </button>
                    </div>
                </div>

                <div class="mt-3 overflow-x-auto">
                    <table class="w-full min-w-[560px] text-sm">
                        <thead class="border-b border-slate-200 text-left text-xs uppercase text-slate-400 dark:border-slate-700">
                            <tr>
                                <th class="px-2 py-2 font-medium">Soal</th>
                                <th class="px-2 py-2 font-medium">Tipe</th>
                                <th class="px-2 py-2 text-right font-medium">Bobot</th>
                                <th class="px-2 py-2 text-right font-medium">Poin</th>
                                <th class="px-2 py-2 text-right font-medium">Berlaku</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="soal in kuis.soal" :key="soal.id" class="border-b border-slate-100 last:border-0 dark:border-slate-700/60">
                                <td class="max-w-xs truncate px-2 py-2 text-slate-700 dark:text-slate-200">{{ soal.pertanyaan }}</td>
                                <td class="px-2 py-2 text-slate-500 dark:text-slate-400">{{ labelTipe[soal.tipe] ?? soal.tipe }}</td>
                                <td class="px-2 py-2 text-right tabular-nums text-slate-500 dark:text-slate-400">{{ soal.bobot }}</td>
                                <td class="px-2 py-2 text-right">
                                    <input
                                        v-model.number="soal.poin"
                                        type="number"
                                        min="0"
                                        max="1000"
                                        placeholder="bawaan"
                                        class="w-28 rounded-lg border-slate-300 text-right text-sm dark:border-slate-600 dark:bg-slate-700"
                                    >
                                </td>
                                <td class="px-2 py-2 text-right tabular-nums text-slate-500 dark:text-slate-400">
                                    {{ poinEfektif(soal.poin, konten.bawaan.soal) }}
                                </td>
                            </tr>
                            <tr v-if="!kuis.soal.length">
                                <td colspan="5" class="px-2 py-4 text-center text-slate-400">Belum ada soal pada kuis ini.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <p class="mt-2 text-xs text-slate-400">
                    Soal essay tidak dinilai otomatis oleh server, sehingga tidak menyumbang poin meski diisi.
                </p>
            </section>

            <div class="sticky bottom-4 flex justify-end">
                <button
                    :disabled="saving"
                    class="rounded-lg bg-teal-600 px-5 py-2.5 text-sm font-medium text-white shadow-lg transition-colors hover:bg-teal-700 disabled:opacity-50"
                    @click="simpan"
                >
                    {{ saving ? 'Menyimpan...' : 'Simpan Pengaturan Poin' }}
                </button>
            </div>
        </template>

        <p v-else class="text-sm text-slate-400">Pilih bab terlebih dahulu untuk mengatur poin.</p>
    </div>
</template>
