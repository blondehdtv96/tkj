<script setup>
import { ref, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import { useAuthStore } from '../../stores/auth';
import { useMateriStore } from '../../stores/materi';

const route = useRoute();
const auth = useAuthStore();
const materiStore = useMateriStore();
const mapelList = ref([]);
const loading = ref(true);
const heading = route.meta.heading ?? 'Materi Pembelajaran';

onMounted(async () => {
    try {
        mapelList.value = await materiStore.fetchMataPelajaran(auth.user?.kelas?.tingkat);
    } finally {
        loading.value = false;
    }
});
</script>

<template>
    <div class="space-y-4">
        <h1 class="text-2xl font-semibold text-primary-900 dark:text-white">{{ heading }}</h1>

        <p v-if="!loading && mapelList.length === 0" class="text-slate-400 text-sm">
            Belum ada mata pelajaran untuk kelasmu.
        </p>

        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <RouterLink
                v-for="mapel in mapelList"
                :key="mapel.id"
                :to="`/siswa/materi/${mapel.id}`"
                class="rounded-xl bg-white dark:bg-slate-800 p-5 shadow-sm hover:shadow-md transition-shadow"
            >
                <h2 class="font-semibold text-primary-900 dark:text-white">{{ mapel.nama }}</h2>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ mapel.jumlah_bab ?? 0 }} bab</p>
            </RouterLink>
        </div>
    </div>
</template>
