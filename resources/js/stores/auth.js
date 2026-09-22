import { defineStore } from 'pinia';
import axios from 'axios';

export const useAuthStore = defineStore('auth', {
    state: () => ({
        user: JSON.parse(localStorage.getItem('user')) || null,
        token: localStorage.getItem('token') || null,
    }),

    getters: {
        isAuthenticated: (state) => !!state.token,
        role: (state) => state.user?.role ?? null,
    },

    actions: {
        async login(credentials) {
            const { data } = await axios.post('/api/login', credentials);

            this.token = data.token;
            this.user = data.user;

            localStorage.setItem('token', data.token);
            localStorage.setItem('user', JSON.stringify(data.user));
            axios.defaults.headers.common.Authorization = `Bearer ${data.token}`;

            // Berisi bonus_streak bila siswa baru pertama kali login hari ini.
            return data;
        },

        async fetchUser() {
            const { data } = await axios.get('/api/user');
            this.user = data.data;
            localStorage.setItem('user', JSON.stringify(data.data));
        },

        async logout() {
            await axios.post('/api/logout');
            this.token = null;
            this.user = null;
            localStorage.removeItem('token');
            localStorage.removeItem('user');
            delete axios.defaults.headers.common.Authorization;
        },
    },
});
