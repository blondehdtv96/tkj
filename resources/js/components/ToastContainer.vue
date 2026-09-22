<script setup>
import { useToastStore } from '../stores/toast';
import Icon from './Icon.vue';

const toast = useToastStore();

const styles = {
    success: 'bg-teal-600 text-white',
    error: 'bg-red-600 text-white',
    info: 'bg-slate-800 text-white',
};

const icons = {
    success: 'check-circle',
    error: 'x-circle',
    info: null,
};
</script>

<template>
    <div class="fixed top-4 right-4 z-50 flex flex-col gap-2 w-[calc(100%-2rem)] max-w-sm">
        <TransitionGroup name="toast">
            <div
                v-for="item in toast.items"
                :key="item.id"
                class="flex items-start gap-3 rounded-lg px-4 py-3 text-sm shadow-lg"
                :class="styles[item.type] ?? styles.info"
            >
                <Icon v-if="icons[item.type]" :name="icons[item.type]" :size="18" class="mt-0.5" />
                <span class="flex-1">{{ item.message }}</span>
                <button class="opacity-70 hover:opacity-100" @click="toast.remove(item.id)">
                    <Icon name="close" :size="16" />
                </button>
            </div>
        </TransitionGroup>
    </div>
</template>

<style scoped>
.toast-enter-active,
.toast-leave-active {
    transition: all 0.2s ease;
}
.toast-enter-from {
    opacity: 0;
    transform: translateY(-8px);
}
.toast-leave-to {
    opacity: 0;
    transform: translateX(16px);
}
</style>
