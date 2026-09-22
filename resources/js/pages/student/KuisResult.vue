<script setup>
import { ref, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import axios from 'axios';
import Icon from '../../components/Icon.vue';

const route = useRoute();
const hasil = ref(null);
const loading = ref(true);

/**
 * Kunci jawaban soal troubleshooting, terurut sesuai kolom urutan.
 */
function langkahKunci(soal) {
    return [...(soal.opsi_jawaban ?? [])].sort((a, b) => (a.urutan ?? 0) - (b.urutan ?? 0));
}

function teksOpsi(soal, opsiId) {
    return soal.opsi_jawaban?.find((o) => o.id === opsiId)?.teks ?? '-';
}

onMounted(async () => {
    try {
        const { data } = await axios.get(`/api/hasil-kuis/${route.params.hasilKuisId}`);
        hasil.value = data.data;
    } finally {
        loading.value = false;
    }
});
</script>

<template>
    <div v-if="hasil" class="max-w-3xl space-y-4">
        <div class="rounded-xl bg-white dark:bg-slate-800 p-6 shadow-sm text-center">
            <p class="text-sm text-slate-500 dark:text-slate-400">{{ hasil.kuis?.judul }}</p>
            <p
                class="mt-1 text-4xl font-bold"
                :class="hasil.skor >= hasil.kuis?.kkm ? 'text-teal-600' : 'text-red-500'"
            >
                {{ hasil.skor ?? '-' }}
            </p>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">KKM: {{ hasil.kuis?.kkm }}</p>
            <p class="mt-2 font-medium" :class="hasil.skor >= hasil.kuis?.kkm ? 'text-teal-600' : 'text-red-500'">
                {{ hasil.skor >= hasil.kuis?.kkm ? 'Lulus' : 'Belum Lulus' }}
            </p>
        </div>

        <h2 class="font-semibold text-primary-900 dark:text-white">Pembahasan</h2>

        <div
            v-for="(jawaban, index) in hasil.jawaban"
            :key="jawaban.id"
            class="rounded-xl bg-white dark:bg-slate-800 p-5 shadow-sm"
        >
            <p
                v-if="jawaban.soal.skenario"
                class="mb-3 rounded-lg border-l-4 border-amber-400 bg-amber-50 px-3 py-2 text-sm text-amber-900 dark:bg-amber-900/20 dark:text-amber-100"
            >
                <span class="font-semibold">Skenario:</span> {{ jawaban.soal.skenario }}
            </p>

            <p class="font-medium text-slate-800 dark:text-slate-100">{{ index + 1 }}. {{ jawaban.soal.pertanyaan }}</p>

            <!-- Troubleshooting: bandingkan urutan siswa dengan kunci -->
            <div v-if="jawaban.soal.tipe === 'troubleshooting'" class="mt-2 space-y-3">
                <p class="text-sm text-slate-500 dark:text-slate-400">
                    Posisi tepat:
                    <span class="font-semibold text-slate-700 dark:text-slate-200">
                        {{ Math.round((jawaban.porsi_benar ?? 0) * langkahKunci(jawaban.soal).length) }}
                        dari {{ langkahKunci(jawaban.soal).length }}
                    </span>
                </p>

                <div class="grid gap-3 sm:grid-cols-2">
                    <div>
                        <p class="mb-1 text-xs font-medium uppercase text-slate-400">Urutanmu</p>
                        <ol class="space-y-1">
                            <li
                                v-for="(opsiId, posisi) in (jawaban.jawaban_urutan ?? [])"
                                :key="opsiId"
                                class="flex items-center gap-2 rounded-lg px-3 py-1.5 text-sm"
                                :class="langkahKunci(jawaban.soal)[posisi]?.id === opsiId
                                    ? 'bg-teal-50 text-teal-700 dark:bg-teal-900/40 dark:text-teal-200'
                                    : 'bg-red-50 text-red-700 dark:bg-red-900/40 dark:text-red-200'"
                            >
                                <Icon :name="langkahKunci(jawaban.soal)[posisi]?.id === opsiId ? 'check' : 'close'" :size="16" />
                                <span>{{ posisi + 1 }}. {{ teksOpsi(jawaban.soal, opsiId) }}</span>
                            </li>
                            <li v-if="!(jawaban.jawaban_urutan ?? []).length" class="text-sm text-slate-400">Tidak dijawab.</li>
                        </ol>
                    </div>

                    <div>
                        <p class="mb-1 text-xs font-medium uppercase text-slate-400">Urutan yang benar</p>
                        <ol class="space-y-1">
                            <li
                                v-for="(langkah, posisi) in langkahKunci(jawaban.soal)"
                                :key="langkah.id"
                                class="rounded-lg bg-slate-50 px-3 py-1.5 text-sm text-slate-700 dark:bg-slate-700/50 dark:text-slate-200"
                            >
                                {{ posisi + 1 }}. {{ langkah.teks }}
                            </li>
                        </ol>
                    </div>
                </div>
            </div>

            <ul v-else-if="jawaban.soal.tipe !== 'essay'" class="mt-2 space-y-1">
                <li
                    v-for="opsi in jawaban.soal.opsi_jawaban"
                    :key="opsi.id"
                    class="flex items-center gap-2 rounded-lg px-3 py-1.5 text-sm"
                    :class="{
                        'bg-teal-50 text-teal-700 dark:bg-teal-900/40 dark:text-teal-200': opsi.is_benar,
                        'bg-red-50 text-red-700 dark:bg-red-900/40 dark:text-red-200': !opsi.is_benar && opsi.id === jawaban.opsi_id,
                    }"
                >
                    <Icon v-if="opsi.is_benar" name="check" :size="16" />
                    <Icon v-else-if="opsi.id === jawaban.opsi_id" name="close" :size="16" />
                    <span v-else class="inline-block w-4" />
                    <span>{{ opsi.teks }}</span>
                </li>
            </ul>
            <p v-else class="mt-2 text-sm text-slate-600 dark:text-slate-300">
                Jawabanmu: {{ jawaban.jawaban_teks || '(kosong, perlu dinilai guru)' }}
            </p>

            <p v-if="jawaban.soal.pembahasan" class="mt-3 text-sm text-slate-500 dark:text-slate-400 border-t border-slate-100 dark:border-slate-700 pt-2">
                <strong>Pembahasan:</strong> {{ jawaban.soal.pembahasan }}
            </p>
        </div>
    </div>
</template>
