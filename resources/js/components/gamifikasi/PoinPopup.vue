<script setup>
import { useGamifikasiStore } from '../../stores/gamifikasi';
import Icon from '../Icon.vue';

const gamifikasi = useGamifikasiStore();
</script>

<template>
    <div class="pointer-events-none fixed inset-x-0 top-20 z-50 flex flex-col items-center gap-2 px-4">
        <TransitionGroup name="poin">
            <div
                v-for="item in gamifikasi.perayaan"
                :key="item.id"
                class="flex max-w-sm items-center gap-2.5 rounded-full bg-gradient-to-r from-teal-500 to-primary-600 px-4 py-2 text-white shadow-lg ring-1 ring-white/20"
            >
                <Icon name="coin" :size="18" />
                <span class="text-base font-bold tabular-nums">+{{ item.jumlah }}</span>
                <span v-if="item.keterangan" class="truncate text-sm text-white/85">{{ item.keterangan }}</span>
            </div>
        </TransitionGroup>
    </div>
</template>

<style scoped>
.poin-enter-active {
    transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
}

.poin-leave-active {
    transition: all 0.5s ease-in;
}

.poin-enter-from {
    opacity: 0;
    transform: translateY(14px) scale(0.85);
}

.poin-leave-to {
    opacity: 0;
    transform: translateY(-22px) scale(0.95);
}

.poin-move {
    transition: transform 0.3s ease;
}

@media (prefers-reduced-motion: reduce) {
    .poin-enter-active,
    .poin-leave-active,
    .poin-move {
        transition: none;
    }
}
</style>
