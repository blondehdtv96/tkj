<script setup>
import Icon from './Icon.vue';

defineProps({
    icon: { type: String, required: true },
    label: { type: String, required: true },
    value: { type: [String, Number], default: '-' },
    tone: { type: String, default: 'primary' },
});

const toneClasses = {
    primary: 'bg-primary-50 text-primary-600 dark:bg-primary-900/30 dark:text-primary-300',
    teal: 'bg-teal-50 text-teal-600 dark:bg-teal-900/30 dark:text-teal-300',
    amber: 'bg-amber-50 text-amber-600 dark:bg-amber-900/30 dark:text-amber-300',
    red: 'bg-red-50 text-red-600 dark:bg-red-900/30 dark:text-red-300',
    slate: 'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300',
};
</script>

<template>
    <div class="flex items-center gap-4 rounded-xl bg-white dark:bg-slate-800 p-5 shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md">
        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-lg" :class="toneClasses[tone] ?? toneClasses.primary">
            <Icon :name="icon" :size="22" />
        </div>
        <div class="min-w-0">
            <p class="text-xs font-medium uppercase leading-snug text-slate-400">{{ label }}</p>
            <p class="mt-1 truncate text-2xl font-bold text-primary-900 dark:text-white">{{ value }}</p>
        </div>
    </div>
</template>
