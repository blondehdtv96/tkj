<script setup>
import { RouterView } from 'vue-router';
import Icon from '../components/Icon.vue';

const fitur = [
    'Materi lengkap Teknik Komputer dan Jaringan kelas 10-12',
    'Kuis interaktif dengan koreksi otomatis dan pembahasan',
    'Simulator subnetting, OSI layer, topologi, dan CLI jaringan',
];
</script>

<template>
    <div class="min-h-screen flex flex-col lg:flex-row bg-white dark:bg-slate-900">
        <div class="relative overflow-hidden bg-gradient-to-br from-primary-800 via-primary-900 to-teal-900 bg-[length:200%_200%] px-6 py-10 sm:px-10 lg:flex lg:w-1/2 lg:flex-col lg:justify-between lg:px-16 lg:py-14 brand-panel">
            <svg class="pointer-events-none absolute -right-16 -top-10 h-72 w-72 text-white/10 lg:h-96 lg:w-96" viewBox="0 0 200 200" fill="none" stroke="currentColor" stroke-width="1.2">
                <g class="net-line">
                    <line x1="150" y1="40" x2="180" y2="90" />
                    <line x1="180" y1="90" x2="120" y2="110" />
                    <line x1="120" y1="110" x2="170" y2="150" />
                    <line x1="170" y1="150" x2="100" y2="170" />
                    <line x1="120" y1="110" x2="150" y2="40" />
                </g>
                <circle class="net-node" cx="150" cy="40" r="3.5" fill="currentColor" stroke="none" style="animation-delay: 0s" />
                <circle class="net-node" cx="180" cy="90" r="3.5" fill="currentColor" stroke="none" style="animation-delay: 0.3s" />
                <circle class="net-node" cx="120" cy="110" r="3.5" fill="currentColor" stroke="none" style="animation-delay: 0.6s" />
                <circle class="net-node" cx="170" cy="150" r="3.5" fill="currentColor" stroke="none" style="animation-delay: 0.9s" />
                <circle class="net-node" cx="100" cy="170" r="3.5" fill="currentColor" stroke="none" style="animation-delay: 1.2s" />
                <circle r="2.2" fill="#5eead4" stroke="none" class="net-packet">
                    <animateMotion
                        dur="4s"
                        repeatCount="indefinite"
                        path="M150,40 L180,90 L120,110 L170,150 L100,170"
                    />
                </circle>
            </svg>

            <div class="relative flex items-center gap-3 brand-in">
                <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-white/10 text-white ring-1 ring-white/15">
                    <Icon name="network" :size="24" />
                </div>
                <div class="text-white">
                    <p class="text-lg font-semibold leading-tight">TKJ Learning</p>
                    <p class="text-xs text-white/60">Teknik Komputer dan Jaringan</p>
                </div>
            </div>

            <div class="relative mt-10 max-w-md lg:mt-0">
                <h1 class="brand-in text-2xl font-semibold text-white sm:text-3xl" style="animation-delay: 90ms">
                    Platform belajar TKJ untuk siswa, guru, dan admin.
                </h1>
                <p class="brand-in mt-3 text-sm text-white/70 sm:text-base" style="animation-delay: 160ms">
                    Satu tempat untuk mengelola materi, bank soal, kuis, dan progres belajar jaringan komputer.
                </p>

                <ul class="mt-8 hidden space-y-3 lg:block">
                    <li
                        v-for="(item, index) in fitur"
                        :key="item"
                        class="brand-in flex items-start gap-2.5 text-sm text-white/80"
                        :style="{ animationDelay: `${230 + index * 80}ms` }"
                    >
                        <span class="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-teal-400/20 text-teal-300">
                            <Icon name="check" :size="12" />
                        </span>
                        {{ item }}
                    </li>
                </ul>
            </div>

            <p class="brand-in relative hidden text-xs text-white/40 lg:block" style="animation-delay: 500ms">
                &copy; {{ new Date().getFullYear() }} TKJ Learning. Dibuat untuk kebutuhan pembelajaran SMK.
            </p>
        </div>

        <div class="flex flex-1 items-center justify-center bg-slate-50 px-4 py-10 dark:bg-slate-900 sm:px-6 lg:w-1/2 lg:px-10">
            <div class="w-full max-w-sm">
                <RouterView />
            </div>
        </div>
    </div>
</template>

<style scoped>
.brand-panel {
    animation: brand-gradient 12s ease infinite;
}

@keyframes brand-gradient {
    0%, 100% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
}

.brand-in {
    animation: brand-in 0.6s cubic-bezier(0.16, 1, 0.3, 1) both;
}

@keyframes brand-in {
    from {
        opacity: 0;
        transform: translateY(12px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.net-node {
    transform-box: fill-box;
    transform-origin: center;
    animation: net-pulse 2.4s ease-in-out infinite;
}

@keyframes net-pulse {
    0%, 100% { opacity: 0.55; transform: scale(1); }
    50% { opacity: 1; transform: scale(1.6); }
}

.net-line {
    stroke-dasharray: 6 4;
    animation: net-flow 6s linear infinite;
}

@keyframes net-flow {
    to { stroke-dashoffset: -100; }
}

@media (prefers-reduced-motion: reduce) {
    .brand-panel,
    .brand-in,
    .net-node,
    .net-line,
    .net-packet {
        animation: none !important;
    }
}
</style>
