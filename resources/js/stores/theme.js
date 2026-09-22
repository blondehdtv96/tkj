import { defineStore } from 'pinia';

function applyTheme(isDark) {
    document.documentElement.classList.toggle('dark', isDark);
}

export const useThemeStore = defineStore('theme', {
    state: () => ({
        dark: false,
    }),

    actions: {
        init() {
            const stored = localStorage.getItem('theme');
            this.dark = stored ? stored === 'dark' : window.matchMedia('(prefers-color-scheme: dark)').matches;
            applyTheme(this.dark);
        },

        toggle() {
            this.dark = ! this.dark;
            localStorage.setItem('theme', this.dark ? 'dark' : 'light');
            applyTheme(this.dark);
        },
    },
});
