<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../../stores/auth';
import { useGamifikasiStore } from '../../stores/gamifikasi';
import Icon from '../../components/Icon.vue';

const auth = useAuthStore();
const gamifikasi = useGamifikasiStore();
const router = useRouter();

const identifier = ref('');
const password = ref('');
const showPassword = ref(false);
const error = ref('');
const loading = ref(false);

const roleHome = {
    admin: '/admin/dashboard',
    guru: '/guru/dashboard',
    siswa: '/siswa/dashboard',
};

async function submit() {
    error.value = '';
    loading.value = true;
    try {
        const hasil = await auth.login({ identifier: identifier.value, password: password.value });
        router.push(roleHome[auth.role] ?? '/');

        if (hasil?.bonus_streak) {
            gamifikasi.rayakan(
                hasil.bonus_streak.jumlah,
                `Streak hari ke-${hasil.bonus_streak.streak_hari}`,
            );
        }
    } catch (e) {
        error.value = e.response?.data?.message
            ?? e.response?.data?.errors?.identifier?.[0]
            ?? 'Login gagal.';
    } finally {
        loading.value = false;
    }
}
</script>

<template>
    <div class="login-in rounded-2xl border border-slate-100 bg-white p-8 shadow-xl shadow-slate-200/60 dark:border-slate-700/50 dark:bg-slate-800 dark:shadow-none">
        <h1 class="text-xl font-semibold text-primary-900 dark:text-white">Masuk ke akun Anda</h1>
        <p class="mt-1.5 text-sm leading-relaxed text-slate-500 dark:text-slate-400">
            Gunakan NIS (siswa) atau username (guru/admin) untuk melanjutkan.
        </p>

        <form class="mt-7 space-y-5" @submit.prevent="submit">
            <div class="login-in" style="animation-delay: 80ms">
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">NIS / Username</label>
                <div class="group relative mt-2">
                    <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400 transition-colors group-focus-within:text-primary-600 dark:group-focus-within:text-primary-400">
                        <Icon name="user" :size="18" />
                    </span>
                    <input
                        v-model="identifier"
                        type="text"
                        autocomplete="username"
                        required
                        placeholder="NIS atau username"
                        class="block w-full rounded-lg border-slate-300 bg-white py-2.5 pl-11 pr-3 text-sm text-slate-800 shadow-sm transition-all duration-200 placeholder:text-slate-400 hover:border-slate-400 focus:border-primary-500 focus:shadow-md focus:shadow-primary-500/10 focus:ring-4 focus:ring-primary-500/15 dark:border-slate-600 dark:bg-slate-700/60 dark:text-white dark:placeholder:text-slate-500 dark:hover:border-slate-500"
                    />
                </div>
            </div>

            <div class="login-in" style="animation-delay: 150ms">
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Password</label>
                <div class="group relative mt-2">
                    <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400 transition-colors group-focus-within:text-primary-600 dark:group-focus-within:text-primary-400">
                        <Icon name="lock" :size="18" />
                    </span>
                    <input
                        v-model="password"
                        :type="showPassword ? 'text' : 'password'"
                        autocomplete="current-password"
                        required
                        placeholder="Kata sandi"
                        class="block w-full rounded-lg border-slate-300 bg-white py-2.5 pl-11 pr-11 text-sm text-slate-800 shadow-sm transition-all duration-200 placeholder:text-slate-400 hover:border-slate-400 focus:border-primary-500 focus:shadow-md focus:shadow-primary-500/10 focus:ring-4 focus:ring-primary-500/15 dark:border-slate-600 dark:bg-slate-700/60 dark:text-white dark:placeholder:text-slate-500 dark:hover:border-slate-500"
                    />
                    <button
                        type="button"
                        class="absolute inset-y-0 right-0 flex items-center pr-3.5 text-slate-400 transition-colors hover:text-primary-600 dark:hover:text-primary-400"
                        :aria-label="showPassword ? 'Sembunyikan password' : 'Tampilkan password'"
                        @click="showPassword = !showPassword"
                    >
                        <Icon :name="showPassword ? 'eye-off' : 'eye'" :size="18" />
                    </button>
                </div>
            </div>

            <Transition name="alert">
                <div
                    v-if="error"
                    class="flex items-start gap-2 rounded-lg border border-red-200 bg-red-50 px-3.5 py-2.5 text-sm text-red-700 dark:border-red-900/50 dark:bg-red-900/20 dark:text-red-300"
                >
                    <Icon name="x-circle" :size="16" class="mt-0.5 shrink-0" />
                    <span>{{ error }}</span>
                </div>
            </Transition>

            <button
                type="submit"
                :disabled="loading"
                class="login-in group relative flex w-full items-center justify-center gap-2 overflow-hidden rounded-lg bg-primary-600 px-4 py-2.5 text-sm font-medium text-white shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:bg-primary-700 hover:shadow-lg hover:shadow-primary-600/25 active:translate-y-0 disabled:cursor-not-allowed disabled:translate-y-0 disabled:opacity-60 disabled:shadow-none"
                style="animation-delay: 220ms"
            >
                <Icon v-if="loading" name="spinner" :size="16" class="animate-spin" />
                {{ loading ? 'Memproses...' : 'Masuk' }}
            </button>
        </form>
    </div>
</template>

<style scoped>
.login-in {
    animation: login-in 0.5s cubic-bezier(0.16, 1, 0.3, 1) both;
}

@keyframes login-in {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.alert-enter-active {
    transition: all 0.25s ease;
}
.alert-leave-active {
    transition: all 0.15s ease;
}
.alert-enter-from,
.alert-leave-to {
    opacity: 0;
    transform: translateY(-6px);
}

@media (prefers-reduced-motion: reduce) {
    .login-in {
        animation: none;
    }
}
</style>
