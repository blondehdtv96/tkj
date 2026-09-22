<script setup>
import { ref, onMounted, watch, computed } from 'vue';
import { useRoute } from 'vue-router';
import { useMateriStore } from '../../stores/materi';
import { useToastStore } from '../../stores/toast';
import { useGamifikasiStore } from '../../stores/gamifikasi';
import PemahamanQuiz from '../../components/gamifikasi/PemahamanQuiz.vue';
import SkeletonBlock from '../../components/SkeletonBlock.vue';
import Icon from '../../components/Icon.vue';

const route = useRoute();
const materiStore = useMateriStore();
const toast = useToastStore();
const gamifikasi = useGamifikasiStore();

const loading = ref(true);
const menandai = ref(false);
const kuisSiap = ref(false);
const pemahamanRef = ref(null);

const materi = computed(() => materiStore.currentMateri);

async function load() {
    loading.value = true;
    kuisSiap.value = false;

    // Dikosongkan dulu agar materi sebelumnya tidak sempat terlihat saat berpindah.
    materiStore.currentMateri = null;

    try {
        await materiStore.fetchMateri(route.params.materiId);
    } catch {
        // Pesan error sudah ditampilkan axios interceptor; halaman menampilkan
        // panel "materi tidak ditemukan" karena currentMateri tetap null.
    } finally {
        loading.value = false;
    }
}

async function tandaiSelesai() {
    menandai.value = true;
    try {
        const hasil = await materiStore.tandaiSelesai(
            materi.value.id,
            pemahamanRef.value?.ambilJawaban() ?? [],
        );

        if (hasil.poin_diperoleh > 0) {
            gamifikasi.rayakan(hasil.poin_diperoleh, 'Materi selesai');
            gamifikasi.sinkronkanPoinAuth(hasil.total_poin);
        }

        toast.success('Materi ditandai selesai. Kuis terbuka setelah semua materi pada bab ini tuntas.');
    } finally {
        menandai.value = false;
    }
}

onMounted(load);
watch(() => route.params.materiId, load);
</script>

<template>
    <div v-if="loading" class="max-w-3xl space-y-4">
        <SkeletonBlock />
        <div class="rounded-xl bg-white dark:bg-slate-800 p-5 shadow-sm">
            <SkeletonBlock />
        </div>
    </div>

    <div v-else-if="!materi" class="max-w-lg rounded-xl bg-white dark:bg-slate-800 p-6 text-center shadow-sm">
        <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-amber-50 text-amber-500 dark:bg-amber-900/30 dark:text-amber-300">
            <Icon name="book" :size="24" />
        </div>
        <h1 class="mt-3 font-semibold text-primary-900 dark:text-white">Materi Tidak Ditemukan</h1>
        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
            Materi ini mungkin sudah dihapus atau tautannya tidak berlaku lagi.
        </p>
        <RouterLink
            to="/siswa/materi"
            class="mt-4 inline-block rounded-lg bg-primary-600 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-primary-700"
        >
            Kembali ke Daftar Materi
        </RouterLink>
    </div>

    <div v-else class="space-y-4 max-w-3xl">
        <div class="flex items-start justify-between gap-4">
            <h1 class="text-2xl font-semibold text-primary-900 dark:text-white">{{ materi.judul }}</h1>
            <span
                v-if="materi.selesai"
                class="shrink-0 rounded-full bg-teal-100 text-teal-700 text-xs font-semibold px-3 py-1 dark:bg-teal-900 dark:text-teal-200"
            >
                Selesai
            </span>
        </div>

        <div class="rounded-xl bg-white dark:bg-slate-800 p-5 shadow-sm">
            <div v-if="materi.tipe === 'teks'" class="prose prose-sm max-w-none dark:prose-invert" v-html="materi.konten_html" />

            <video v-else-if="materi.tipe === 'video'" :src="materi.file_url" controls class="w-full rounded-lg" />

            <iframe v-else-if="materi.tipe === 'pdf'" :src="materi.file_url" class="w-full h-[70vh] rounded-lg border" />
        </div>

        <PemahamanQuiz
            v-if="!materi.selesai"
            ref="pemahamanRef"
            :materi-id="materi.id"
            @siap="kuisSiap = $event"
        />

        <div v-if="!materi.selesai" class="flex flex-wrap items-center gap-3">
            <button
                :disabled="menandai || !kuisSiap"
                class="rounded-lg bg-teal-600 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-teal-700 disabled:cursor-not-allowed disabled:opacity-50"
                @click="tandaiSelesai"
            >
                {{ menandai ? 'Menyimpan...' : 'Tandai Selesai' }}
            </button>

            <span class="inline-flex items-center gap-1.5 text-sm text-slate-500 dark:text-slate-400">
                <Icon name="coin" :size="15" class="text-amber-500" />
                Selesaikan materi ini untuk mendapatkan poin.
            </span>
        </div>
    </div>
</template>
