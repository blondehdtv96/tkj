<script setup>
import { ref, computed } from 'vue';
import { RouterView, RouterLink, useRouter } from 'vue-router';
import { useAuthStore } from '../stores/auth';
import { useThemeStore } from '../stores/theme';
import Icon from '../components/Icon.vue';

defineProps({
    title: { type: String, required: true },
    navItems: { type: Array, default: () => [] },
});

const auth = useAuthStore();
const theme = useThemeStore();
const router = useRouter();
const menuOpen = ref(false);

const roleLabel = {
    admin: 'Administrator',
    guru: 'Guru',
    siswa: 'Siswa',
};

const initial = computed(() => (auth.user?.name?.trim()?.charAt(0) ?? '?').toUpperCase());

async function doLogout() {
    await auth.logout();
    router.push({ name: 'login' });
}
</script>

<template>
    <div class="min-h-screen flex bg-slate-50 dark:bg-slate-900">
        <div
            v-if="menuOpen"
            class="fixed inset-0 z-30 bg-black/40 md:hidden"
            @click="menuOpen = false"
        />

        <aside
            class="fixed inset-y-0 left-0 z-40 w-64 shrink-0 bg-primary-900 text-white flex flex-col transition-transform md:static md:translate-x-0"
            :class="menuOpen ? 'translate-x-0' : '-translate-x-full'"
        >
            <div class="flex items-center gap-2.5 px-5 py-5 border-b border-white/10">
                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-white/10 text-white ring-1 ring-white/15">
                    <Icon name="network" :size="19" />
                </div>
                <span class="text-base font-semibold leading-tight truncate">{{ title }}</span>
            </div>

            <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
                <RouterLink
                    v-for="item in navItems"
                    :key="item.to"
                    :to="item.to"
                    class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-white/70 transition-colors hover:bg-white/10 hover:text-white"
                    active-class="!bg-teal-500/90 !text-white shadow-sm"
                    @click="menuOpen = false"
                >
                    <Icon v-if="item.icon" :name="item.icon" :size="18" />
                    {{ item.label }}
                </RouterLink>
            </nav>

            <div class="px-3 pb-3">
                <div class="flex items-center gap-3 rounded-lg bg-white/5 px-3 py-2.5">
                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-teal-500/90 text-sm font-semibold text-white">
                        {{ initial }}
                    </div>
                    <div class="min-w-0">
                        <p class="truncate text-sm font-medium text-white">{{ auth.user?.name }}</p>
                        <p class="text-xs text-white/50">{{ roleLabel[auth.role] ?? auth.role }}</p>
                    </div>
                </div>
            </div>

            <div class="px-3 py-3 border-t border-white/10 space-y-1">
                <button
                    class="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium text-white/70 hover:bg-white/10 hover:text-white text-left transition-colors"
                    @click="theme.toggle()"
                >
                    <Icon :name="theme.dark ? 'sun' : 'moon'" :size="18" />
                    {{ theme.dark ? 'Mode Terang' : 'Mode Gelap' }}
                </button>
                <button
                    class="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium text-white/70 hover:bg-white/10 hover:text-white text-left transition-colors"
                    @click="doLogout"
                >
                    <Icon name="logout" :size="18" />
                    Keluar
                </button>
            </div>
        </aside>

        <div class="flex-1 flex flex-col min-w-0">
            <header class="flex items-center gap-3 border-b border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 px-4 py-3 md:hidden">
                <button
                    class="rounded-lg p-2 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700"
                    @click="menuOpen = true"
                >
                    <Icon name="menu" :size="20" />
                </button>
                <span class="font-semibold text-primary-900 dark:text-white">{{ title }}</span>
            </header>

            <main class="flex-1 p-4 sm:p-6 min-w-0">
                <RouterView />
            </main>
        </div>
    </div>
</template>
