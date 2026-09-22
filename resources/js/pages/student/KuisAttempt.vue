<script setup>
import { ref, onMounted, onBeforeUnmount, computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useKuisStore } from '../../stores/kuis';
import { useGamifikasiStore } from '../../stores/gamifikasi';
import Icon from '../../components/Icon.vue';

const route = useRoute();
const router = useRouter();
const kuisStore = useKuisStore();
const gamifikasi = useGamifikasiStore();

const loading = ref(true);
const submitting = ref(false);
const error = ref('');
const sisaDetik = ref(0);
let timer = null;

const menit = computed(() => Math.floor(sisaDetik.value / 60));
const detik = computed(() => sisaDetik.value % 60);
const hampirHabis = computed(() => sisaDetik.value <= 60);

function updateSisaWaktu() {
    if (! kuisStore.batasWaktuMs) return;
    sisaDetik.value = Math.max(0, Math.round((kuisStore.batasWaktuMs - Date.now()) / 1000));

    if (sisaDetik.value === 0) {
        submit(true);
    }
}

async function submit(autoSubmit = false) {
    if (submitting.value) return;
    submitting.value = true;
    clearInterval(timer);

    try {
        const hasil = await kuisStore.submitKuis();

        if (hasil.gamifikasi?.poin_diperoleh > 0) {
            gamifikasi.rayakan(hasil.gamifikasi.poin_diperoleh, 'Kuis selesai');
            gamifikasi.sinkronkanPoinAuth(hasil.gamifikasi.total_poin);
        }

        router.push(`/siswa/kuis/hasil/${hasil.id}`);
    } catch (e) {
        if (autoSubmit) {
            // Waktu habis tapi submit gagal (mis. koneksi) - biarkan siswa mencoba manual.
            submitting.value = false;
        } else {
            submitting.value = false;
        }
    }
}

onMounted(async () => {
    try {
        await kuisStore.mulaiKuis(route.params.kuisId);
        updateSisaWaktu();
        timer = setInterval(updateSisaWaktu, 1000);
    } catch (e) {
        error.value = e.response?.data?.errors?.kuis?.[0]
            ?? e.response?.data?.message
            ?? 'Kuis belum dapat dikerjakan.';
    } finally {
        loading.value = false;
    }
});

onBeforeUnmount(() => clearInterval(timer));
</script>

<template>
    <div v-if="error" class="max-w-lg rounded-xl bg-white dark:bg-slate-800 p-6 shadow-sm text-center">
        <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-amber-50 text-amber-500 dark:bg-amber-900/30 dark:text-amber-300">
            <Icon name="lock" :size="24" />
        </div>
        <h1 class="mt-3 font-semibold text-primary-900 dark:text-white">Kuis Belum Terbuka</h1>
        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ error }}</p>
        <RouterLink
            to="/siswa/materi"
            class="mt-4 inline-flex items-center gap-1.5 rounded-lg bg-primary-600 px-4 py-2 text-sm font-medium text-white hover:bg-primary-700"
        >
            <Icon name="book" :size="16" />
            Pelajari Materi Dulu
        </RouterLink>
    </div>

    <div v-else-if="!loading" class="max-w-3xl space-y-4">
        <div class="sticky top-0 z-10 flex items-center justify-between rounded-xl bg-white dark:bg-slate-800 p-4 shadow-sm">
            <h1 class="font-semibold text-primary-900 dark:text-white">{{ kuisStore.currentKuis?.judul }}</h1>
            <div
                class="rounded-lg px-3 py-1.5 text-sm font-mono font-semibold"
                :class="hampirHabis ? 'bg-red-100 text-red-700 dark:bg-red-900 dark:text-red-200' : 'bg-primary-100 text-primary-700 dark:bg-primary-900 dark:text-primary-200'"
            >
                {{ String(menit).padStart(2, '0') }}:{{ String(detik).padStart(2, '0') }}
            </div>
        </div>

        <div
            v-for="(soal, index) in kuisStore.soalList"
            :key="soal.id"
            class="rounded-xl bg-white dark:bg-slate-800 p-5 shadow-sm"
        >
            <p
                v-if="soal.skenario"
                class="mb-3 rounded-lg border-l-4 border-amber-400 bg-amber-50 px-3 py-2 text-sm text-amber-900 dark:bg-amber-900/20 dark:text-amber-100"
            >
                <span class="font-semibold">Skenario:</span> {{ soal.skenario }}
            </p>

            <p class="font-medium text-slate-800 dark:text-slate-100">
                {{ index + 1 }}. {{ soal.pertanyaan }}
            </p>
            <img v-if="soal.gambar_url" :src="soal.gambar_url" class="mt-2 max-w-sm rounded-lg" />

            <!-- Troubleshooting: susun langkah dari pertama ke terakhir -->
            <div v-if="soal.tipe === 'troubleshooting'" class="mt-3 space-y-2">
                <p class="text-xs text-slate-500 dark:text-slate-400">
                    Susun langkah penanganan dari yang paling awal. Posisi yang tepat tetap dinilai walau tidak semuanya benar.
                </p>

                <div
                    v-for="(opsiId, posisi) in kuisStore.urutanLangkah(soal)"
                    :key="opsiId"
                    class="flex items-center gap-2 rounded-lg border border-slate-200 px-3 py-2 text-sm dark:border-slate-600"
                >
                    <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-primary-100 text-xs font-semibold text-primary-700 dark:bg-primary-900/40 dark:text-primary-200">
                        {{ posisi + 1 }}
                    </span>
                    <span class="flex-1 text-slate-700 dark:text-slate-200">
                        {{ soal.opsi_jawaban.find((o) => o.id === opsiId)?.teks }}
                    </span>
                    <button
                        type="button"
                        :disabled="posisi === 0"
                        class="rounded px-2 py-0.5 text-slate-500 hover:bg-slate-100 disabled:opacity-30 dark:hover:bg-slate-700"
                        title="Naikkan langkah"
                        @click="kuisStore.geserLangkah(soal, posisi, -1)"
                    >
                        &uarr;
                    </button>
                    <button
                        type="button"
                        :disabled="posisi === soal.opsi_jawaban.length - 1"
                        class="rounded px-2 py-0.5 text-slate-500 hover:bg-slate-100 disabled:opacity-30 dark:hover:bg-slate-700"
                        title="Turunkan langkah"
                        @click="kuisStore.geserLangkah(soal, posisi, 1)"
                    >
                        &darr;
                    </button>
                </div>
            </div>

            <div v-else-if="soal.tipe !== 'essay'" class="mt-3 space-y-2">
                <label
                    v-for="opsi in soal.opsi_jawaban"
                    :key="opsi.id"
                    class="flex items-center gap-2 rounded-lg border border-slate-200 dark:border-slate-600 px-3 py-2 text-sm cursor-pointer hover:bg-slate-50 dark:hover:bg-slate-700"
                    :class="kuisStore.jawaban[soal.id]?.opsi_id === opsi.id ? 'border-primary-500 bg-primary-50 dark:bg-primary-900/30' : ''"
                >
                    <input
                        type="radio"
                        :name="`soal-${soal.id}`"
                        :checked="kuisStore.jawaban[soal.id]?.opsi_id === opsi.id"
                        @change="kuisStore.jawabSoal(soal.id, opsi.id)"
                    />
                    <span class="text-slate-700 dark:text-slate-200">{{ opsi.teks }}</span>
                </label>
            </div>

            <textarea
                v-else
                rows="4"
                placeholder="Tulis jawabanmu..."
                class="mt-3 w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-700"
                :value="kuisStore.jawaban[soal.id]?.jawaban_teks ?? ''"
                @input="kuisStore.jawabEssay(soal.id, $event.target.value)"
            />
        </div>

        <button
            :disabled="submitting"
            class="w-full rounded-lg bg-primary-600 px-4 py-3 font-medium text-white hover:bg-primary-700 disabled:opacity-50"
            @click="submit(false)"
        >
            {{ submitting ? 'Mengirim...' : 'Selesai & Kumpulkan' }}
        </button>
    </div>
</template>
