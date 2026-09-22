<script setup>
import { useConfirmStore } from '../stores/confirm';

const confirmStore = useConfirmStore();
</script>

<template>
    <Teleport to="body">
        <Transition name="confirm-fade">
            <div
                v-if="confirmStore.visible"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4"
                @click.self="confirmStore.resolve(false)"
            >
                <div class="w-full max-w-sm rounded-xl bg-white dark:bg-slate-800 p-5 shadow-xl">
                    <h2 class="font-semibold text-primary-900 dark:text-white">{{ confirmStore.title }}</h2>
                    <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">{{ confirmStore.message }}</p>
                    <div class="mt-5 flex justify-end gap-2">
                        <button
                            type="button"
                            class="rounded-lg bg-slate-100 dark:bg-slate-700 px-4 py-2 text-sm font-medium text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-600"
                            @click="confirmStore.resolve(false)"
                        >
                            {{ confirmStore.cancelText }}
                        </button>
                        <button
                            type="button"
                            class="rounded-lg px-4 py-2 text-sm font-medium text-white"
                            :class="confirmStore.danger ? 'bg-red-600 hover:bg-red-700' : 'bg-primary-600 hover:bg-primary-700'"
                            @click="confirmStore.resolve(true)"
                        >
                            {{ confirmStore.confirmText }}
                        </button>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

<style scoped>
.confirm-fade-enter-active,
.confirm-fade-leave-active {
    transition: opacity 0.15s ease;
}
.confirm-fade-enter-from,
.confirm-fade-leave-to {
    opacity: 0;
}
</style>
