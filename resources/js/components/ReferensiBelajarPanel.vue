<script setup>
import { ref, computed } from 'vue';
import Icon from './Icon.vue';

const props = defineProps({
    referensi: { type: Object, required: true },
});

const terbuka = ref(false);

const embedUrl = computed(() => {
    const url = props.referensi.video_url;
    if (! url) return null;
    const match = url.match(/(?:youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/embed\/)([\w-]{11})/);
    return match ? `https://www.youtube.com/embed/${match[1]}` : null;
});
</script>

<template>
    <div class="mt-4 overflow-hidden rounded-lg border border-primary-100 dark:border-primary-900/40 bg-primary-50/50 dark:bg-primary-900/10">
        <button
            type="button"
            class="flex w-full items-center justify-between gap-2 px-4 py-3 text-left transition-colors hover:bg-primary-100/50 dark:hover:bg-primary-900/20"
            @click="terbuka = !terbuka"
        >
            <span class="flex items-center gap-2 text-xs font-semibold uppercase text-primary-600 dark:text-primary-300">
                <Icon name="book" :size="14" />
                Referensi Belajar Tambahan
            </span>
            <span class="flex items-center gap-1.5 text-xs text-primary-600 dark:text-primary-300">
                {{ terbuka ? 'Tutup' : 'Buka materi' }}
                <Icon :name="terbuka ? 'chevron-up' : 'chevron-down'" :size="16" />
            </span>
        </button>

        <div v-if="terbuka" class="space-y-4 px-4 pb-4">
            <div v-if="referensi.ringkasan" class="rounded-lg bg-white dark:bg-slate-800 p-4">
                <h4 class="mb-2 flex items-center gap-1.5 text-xs font-semibold uppercase text-slate-500 dark:text-slate-300">
                    <Icon name="book" :size="14" />
                    Ringkasan Materi
                </h4>
                <div class="prose prose-sm max-w-none dark:prose-invert prose-headings:text-primary-900 dark:prose-headings:text-white prose-table:text-xs" v-html="referensi.ringkasan" />
            </div>

            <div v-if="embedUrl" class="rounded-lg bg-white dark:bg-slate-800 p-4">
                <h4 class="mb-2 flex items-center gap-1.5 text-xs font-semibold uppercase text-slate-500 dark:text-slate-300">
                    <Icon name="video" :size="14" />
                    {{ referensi.video_judul || 'Video Pembelajaran' }}
                </h4>
                <div class="aspect-video w-full overflow-hidden rounded-lg bg-slate-900">
                    <iframe
                        :src="embedUrl"
                        class="h-full w-full"
                        loading="lazy"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                        allowfullscreen
                        referrerpolicy="strict-origin-when-cross-origin"
                    />
                </div>
            </div>

            <div v-if="referensi.kasus_soal" class="rounded-lg bg-white dark:bg-slate-800 p-4">
                <h4 class="mb-2 flex items-center gap-1.5 text-xs font-semibold uppercase text-slate-500 dark:text-slate-300">
                    <Icon name="briefcase" :size="14" />
                    Studi Kasus
                </h4>
                <p class="text-sm leading-relaxed text-slate-600 dark:text-slate-200">{{ referensi.kasus_soal }}</p>
            </div>
        </div>
    </div>
</template>
