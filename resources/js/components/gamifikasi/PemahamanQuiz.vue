<script setup>
import { ref, computed, watch } from 'vue';
import axios from 'axios';
import Icon from '../Icon.vue';
import SkeletonBlock from '../SkeletonBlock.vue';

const props = defineProps({
    materiId: { type: [Number, String], required: true },
});

const emit = defineEmits(['siap']);

const soal = ref([]);
const jawaban = ref({});
const loading = ref(true);

const lengkap = computed(() => soal.value.every((item) => jawaban.value[item.id] !== undefined));

async function load() {
    loading.value = true;
    jawaban.value = {};
    try {
        const { data } = await axios.get('/api/soal-pemahaman', { params: { materi_id: props.materiId } });
        soal.value = data.data;
    } finally {
        loading.value = false;
    }
}

watch(() => props.materiId, load, { immediate: true });

// Tombol "Tandai Selesai" baru boleh aktif setelah soal termuat dan terjawab.
watch(
    [loading, lengkap, soal],
    () => emit('siap', ! loading.value && (soal.value.length === 0 || lengkap.value)),
    { immediate: true },
);

/**
 * Dipakai induk untuk menyusun payload POST /api/materi/{id}/selesai.
 */
function ambilJawaban() {
    return soal.value.map((item) => ({ soal_id: item.id, jawaban: jawaban.value[item.id] }));
}

defineExpose({ ambilJawaban, adaSoal: computed(() => soal.value.length > 0) });
</script>

<template>
    <div v-if="loading" class="rounded-xl bg-white dark:bg-slate-800 p-5 shadow-sm">
        <SkeletonBlock />
    </div>

    <div v-else-if="soal.length" class="rounded-xl bg-white dark:bg-slate-800 p-5 shadow-sm">
        <h2 class="flex items-center gap-2 font-semibold text-primary-900 dark:text-white">
            <Icon name="clipboard" :size="16" class="text-primary-500" />
            Cek Pemahaman
        </h2>
        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
            Jawab dengan benar untuk menandai materi ini selesai dan mendapatkan poin.
        </p>

        <div class="mt-4 space-y-5">
            <div v-for="(item, index) in soal" :key="item.id">
                <p class="text-sm font-medium text-slate-700 dark:text-slate-200">
                    {{ index + 1 }}. {{ item.pertanyaan }}
                </p>

                <div class="mt-2 space-y-2">
                    <label
                        v-for="(opsi, i) in item.opsi"
                        :key="i"
                        class="flex cursor-pointer items-start gap-2.5 rounded-lg px-3 py-2 text-sm transition-colors"
                        :class="jawaban[item.id] === i
                            ? 'bg-teal-50 text-teal-800 ring-1 ring-teal-300 dark:bg-teal-900/30 dark:text-teal-100 dark:ring-teal-700'
                            : 'bg-slate-50 text-slate-700 hover:bg-slate-100 dark:bg-slate-700/50 dark:text-slate-200 dark:hover:bg-slate-700'"
                    >
                        <input
                            v-model.number="jawaban[item.id]"
                            type="radio"
                            :name="`pemahaman-${item.id}`"
                            :value="i"
                            class="mt-0.5 text-teal-600 focus:ring-teal-500"
                        >
                        <span>{{ opsi }}</span>
                    </label>
                </div>
            </div>
        </div>
    </div>
</template>
