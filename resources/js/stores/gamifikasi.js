import { defineStore } from 'pinia';
import axios from 'axios';
import { useAuthStore } from './auth';

let nextPerayaanId = 1;

export const useGamifikasiStore = defineStore('gamifikasi', {
    state: () => ({
        ringkasan: null,
        riwayat: [],
        riwayatMeta: null,
        rewards: [],
        redemptions: [],
        loading: false,

        // Antrean animasi "+N poin" yang tampil melayang di pojok layar.
        perayaan: [],
    }),

    getters: {
        totalPoin: (state) => state.ringkasan?.total_poin ?? 0,
        level: (state) => state.ringkasan?.level ?? null,
        lencanaDiraih: (state) => (state.ringkasan?.lencana ?? []).filter((l) => l.diraih),
    },

    actions: {
        async fetchRingkasan(userId = null) {
            const { data } = await axios.get('/api/gamifikasi/ringkasan', {
                params: userId ? { user_id: userId } : {},
            });
            this.ringkasan = data.data;
            return this.ringkasan;
        },

        async fetchRiwayat(params = {}) {
            this.loading = true;
            try {
                const { data } = await axios.get('/api/gamifikasi/poin', { params });
                this.riwayat = data.data;
                this.riwayatMeta = data.meta ?? null;
                return this.riwayat;
            } finally {
                this.loading = false;
            }
        },

        async fetchRewards(params = {}) {
            const { data } = await axios.get('/api/gamifikasi/rewards', { params });
            this.rewards = data.data;
            return this.rewards;
        },

        async fetchRedemptions(params = {}) {
            const { data } = await axios.get('/api/gamifikasi/redemptions', { params });
            this.redemptions = data.data;
            return { data: this.redemptions, meta: data.meta ?? null };
        },

        async fetchLeaderboard(params = {}) {
            const { data } = await axios.get('/api/gamifikasi/leaderboard', { params });
            return { data: data.data, meta: data.meta };
        },

        async ajukanRedeem(payload) {
            const { data } = await axios.post('/api/gamifikasi/redemptions', payload);
            await this.fetchRingkasan();
            this.sinkronkanPoinAuth();
            return data.data;
        },

        async ubahStatusRedeem(id, payload) {
            const { data } = await axios.post(`/api/gamifikasi/redemptions/${id}/status`, payload);
            return data.data;
        },

        /**
         * Menampilkan animasi perolehan poin. Dipanggil setelah endpoint yang
         * memberi poin mengembalikan jumlahnya.
         */
        rayakan(jumlah, keterangan = '') {
            if (! jumlah) return;

            const id = nextPerayaanId++;
            this.perayaan.push({ id, jumlah, keterangan });
            setTimeout(() => {
                this.perayaan = this.perayaan.filter((item) => item.id !== id);
            }, 2600);
        },

        /**
         * Menyalin total poin terbaru ke authStore agar angka di sidebar dan
         * katalog reward ikut berubah tanpa perlu memuat ulang halaman.
         */
        sinkronkanPoinAuth(totalPoin = null) {
            const auth = useAuthStore();
            const nilai = totalPoin ?? this.ringkasan?.total_poin;

            if (! auth.user || nilai === null || nilai === undefined) return;

            auth.user = { ...auth.user, total_poin: nilai, level: this.ringkasan?.level?.level ?? auth.user.level };
            localStorage.setItem('user', JSON.stringify(auth.user));
        },
    },
});
