<script setup>
import Icon from '../Icon.vue';

defineProps({
    lencana: { type: Array, default: () => [] },
});

function persen(item) {
    return item.target > 0 ? Math.min(100, Math.round((item.progres / item.target) * 100)) : 0;
}
</script>

<template>
    <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
        <div
            v-for="item in lencana"
            :key="item.kode"
            class="flex items-start gap-3 rounded-xl border p-3.5 transition-all duration-200"
            :class="item.diraih
                ? 'border-teal-200 bg-teal-50 dark:border-teal-800 dark:bg-teal-900/20'
                : 'border-slate-200 bg-white dark:border-slate-700 dark:bg-slate-800'"
        >
            <div
                class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg"
                :class="item.diraih
                    ? 'bg-teal-500 text-white'
                    : 'bg-slate-100 text-slate-400 dark:bg-slate-700 dark:text-slate-500'"
            >
                <Icon :name="item.ikon" :size="20" />
            </div>

            <div class="min-w-0 flex-1">
                <p
                    class="text-sm font-semibold"
                    :class="item.diraih ? 'text-teal-800 dark:text-teal-200' : 'text-slate-600 dark:text-slate-300'"
                >
                    {{ item.nama }}
                </p>
                <p class="mt-0.5 text-xs leading-snug text-slate-500 dark:text-slate-400">{{ item.deskripsi }}</p>

                <div v-if="!item.diraih" class="mt-2">
                    <div class="h-1.5 w-full overflow-hidden rounded-full bg-slate-200 dark:bg-slate-700">
                        <div class="h-full rounded-full bg-primary-400 transition-all duration-500" :style="{ width: `${persen(item)}%` }" />
                    </div>
                    <p class="mt-1 text-[11px] tabular-nums text-slate-400">{{ item.progres }} / {{ item.target }}</p>
                </div>

                <p v-else class="mt-2 inline-flex items-center gap-1 text-[11px] font-semibold uppercase tracking-wide text-teal-600 dark:text-teal-400">
                    <Icon name="check" :size="12" />
                    Diraih
                </p>
            </div>
        </div>

        <p v-if="lencana.length === 0" class="text-sm text-slate-400">Belum ada lencana yang tersedia.</p>
    </div>
</template>
