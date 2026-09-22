<script setup>
defineProps({
    rows: { type: Number, default: 3 },
});
</script>

<template>
    <div class="animate-pulse space-y-3">
        <div v-for="i in rows" :key="i" class="h-4 rounded bg-slate-200 dark:bg-slate-700" :style="{ width: i % 2 ? '100%' : '75%' }" />
    </div>
</template>
