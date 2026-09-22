<script setup>
import { computed } from 'vue';
import Icon from '../Icon.vue';

const props = defineProps({
    level: { type: Object, default: null },
    totalPoin: { type: Number, default: 0 },
    streakHari: { type: Number, default: 0 },
});

const persentase = computed(() => Math.min(100, Math.max(0, props.level?.persentase ?? 0)));
</script>

<template>
    <div class="rounded-xl bg-gradient-to-br from-primary-700 to-teal-600 p-5 text-white shadow-sm">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-white/15 ring-1 ring-white/20">
                    <Icon :name="level?.ikon ?? 'user'" :size="24" />
                </div>
                <div class="min-w-0">
                    <p class="text-xs font-medium uppercase tracking-wide text-white/60">Level Saat Ini</p>
                    <p class="truncate text-xl font-bold leading-tight">{{ level?.level ?? 'Pemula' }}</p>
                </div>
            </div>

            <div class="flex items-center gap-4">
                <div class="text-right">
                    <p class="text-xs font-medium uppercase tracking-wide text-white/60">Total Poin</p>
                    <p class="text-2xl font-bold tabular-nums leading-tight">{{ totalPoin.toLocaleString('id-ID') }}</p>
                </div>
                <div
                    v-if="streakHari > 0"
                    class="flex items-center gap-1.5 rounded-lg bg-white/15 px-2.5 py-1.5 text-sm font-semibold ring-1 ring-white/20"
                    :title="`Belajar ${streakHari} hari berturut-turut`"
                >
                    <Icon name="flame" :size="16" />
                    {{ streakHari }}
                </div>
            </div>
        </div>

        <div class="mt-5">
            <div class="h-2.5 w-full overflow-hidden rounded-full bg-white/20">
                <div
                    class="h-full rounded-full bg-white transition-all duration-700 ease-out"
                    :style="{ width: `${persentase}%` }"
                />
            </div>

            <p class="mt-2 text-sm text-white/80">
                <template v-if="level?.level_berikutnya">
                    Kurang <span class="font-semibold text-white">{{ level.poin_ke_level_berikutnya.toLocaleString('id-ID') }} poin</span>
                    menuju {{ level.level_berikutnya }}.
                </template>
                <template v-else>
                    Level tertinggi tercapai. Pertahankan, Master Technician!
                </template>
            </p>
        </div>
    </div>
</template>
