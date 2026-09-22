<script setup>
import { ref, onMounted, onBeforeUnmount, watch } from 'vue';
import { useRoute } from 'vue-router';
import { useMateriStore } from '../../stores/materi';
import ReferensiBelajarPanel from '../../components/ReferensiBelajarPanel.vue';
import Icon from '../../components/Icon.vue';

const route = useRoute();
const materiStore = useMateriStore();
const babList = ref([]);
const loading = ref(true);

function jumlahSelesai(bab) {
    return (bab.materi ?? []).filter((m) => m.selesai).length;
}

async function load({ senyap = false } = {}) {
    loading.value = ! senyap;
    try {
        babList.value = await materiStore.fetchBab(route.params.mataPelajaranId);
    } finally {
        loading.value = false;
    }
}

// Materi dibuka di tab terpisah, jadi progres disegarkan diam-diam saat siswa
// kembali ke tab ini agar status "selesai" dan kunci kuis ikut ter-update.
function segarkanSaatKembali() {
    if (document.visibilityState === 'visible') {
        load({ senyap: true });
    }
}

onMounted(() => {
    load();
    document.addEventListener('visibilitychange', segarkanSaatKembali);
});

onBeforeUnmount(() => document.removeEventListener('visibilitychange', segarkanSaatKembali));

watch(() => route.params.mataPelajaranId, () => load());
</script>

<template>
    <div class="space-y-4">
        <h1 class="text-2xl font-semibold text-primary-900 dark:text-white">Daftar Bab</h1>

        <p v-if="!loading && babList.length === 0" class="text-slate-400 text-sm">Belum ada bab tersedia.</p>

        <div
            v-for="bab in babList"
            :key="bab.id"
            class="rounded-xl bg-white dark:bg-slate-800 p-5 shadow-sm"
        >
            <div class="flex flex-wrap items-center justify-between gap-2">
                <h2 class="font-semibold text-primary-900 dark:text-white">{{ bab.urutan }}. {{ bab.judul }}</h2>
                <span
                    v-if="bab.materi?.length"
                    class="rounded-full px-2.5 py-0.5 text-xs font-medium"
                    :class="jumlahSelesai(bab) === bab.materi.length
                        ? 'bg-teal-100 text-teal-700 dark:bg-teal-900 dark:text-teal-200'
                        : 'bg-slate-100 text-slate-500 dark:bg-slate-700 dark:text-slate-300'"
                >
                    Materi selesai {{ jumlahSelesai(bab) }}/{{ bab.materi.length }}
                </span>
            </div>

            <div class="mt-3 grid gap-2 sm:grid-cols-2">
                <div>
                    <h3 class="text-xs font-semibold uppercase text-slate-400 mb-1">Materi</h3>
                    <ul class="space-y-1">
                        <li v-for="materi in bab.materi" :key="materi.id" class="flex items-start gap-1.5">
                            <Icon
                                :name="materi.selesai ? 'check-circle' : 'book'"
                                :size="14"
                                class="mt-0.5"
                                :class="materi.selesai ? 'text-teal-500' : 'text-slate-300 dark:text-slate-600'"
                            />
                            <RouterLink
                                :to="`/siswa/materi/detail/${materi.id}`"
                                target="_blank"
                                rel="noopener"
                                title="Dibuka di tab baru agar lebih fokus belajar"
                                class="group text-sm text-primary-700 dark:text-primary-300 hover:underline"
                            >
                                {{ materi.judul }}
                                <Icon
                                    name="external-link"
                                    :size="12"
                                    class="ml-0.5 inline-block align-baseline text-slate-300 group-hover:text-primary-500 dark:text-slate-600"
                                />
                            </RouterLink>
                        </li>
                        <li v-if="!bab.materi?.length" class="text-sm text-slate-400">Belum ada materi.</li>
                    </ul>
                </div>

                <div>
                    <h3 class="text-xs font-semibold uppercase text-slate-400 mb-1">Kuis</h3>
                    <ul class="space-y-1">
                        <li v-for="kuis in bab.kuis" :key="kuis.id">
                            <RouterLink
                                v-if="!kuis.terkunci"
                                :to="`/siswa/kuis/${kuis.id}/kerjakan`"
                                class="flex items-start gap-1.5 text-sm text-teal-700 dark:text-teal-300 hover:underline"
                            >
                                <Icon name="clipboard" :size="14" class="mt-0.5 shrink-0" />
                                {{ kuis.judul }} ({{ kuis.durasi_menit }} menit)
                            </RouterLink>
                            <div v-else class="flex items-start gap-1.5 text-sm text-slate-400" :title="`Selesaikan ${kuis.materi_belum_selesai} materi terlebih dahulu`">
                                <Icon name="lock" :size="14" class="mt-0.5 shrink-0" />
                                <span>
                                    {{ kuis.judul }}
                                    <span class="block text-xs text-amber-600 dark:text-amber-400">
                                        Terkunci &middot; selesaikan {{ kuis.materi_belum_selesai }} materi dulu
                                    </span>
                                </span>
                            </div>
                        </li>
                        <li v-if="!bab.kuis?.length" class="text-sm text-slate-400">Belum ada kuis.</li>
                    </ul>
                </div>
            </div>

            <ReferensiBelajarPanel v-if="bab.referensi_belajar" :referensi="bab.referensi_belajar" />
        </div>
    </div>
</template>
