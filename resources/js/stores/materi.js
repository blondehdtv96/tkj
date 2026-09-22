import { defineStore } from 'pinia';
import axios from 'axios';

export const useMateriStore = defineStore('materi', {
    state: () => ({
        mataPelajaranList: [],
        babList: [],
        currentMateri: null,
        loading: false,
    }),

    actions: {
        async fetchMataPelajaran(tingkat = null) {
            const { data } = await axios.get('/api/mata-pelajaran', {
                params: tingkat ? { tingkat } : {},
            });
            this.mataPelajaranList = data.data;
            return this.mataPelajaranList;
        },

        async fetchBab(mataPelajaranId) {
            this.loading = true;
            try {
                const { data } = await axios.get('/api/bab', {
                    params: { mata_pelajaran_id: mataPelajaranId },
                });
                this.babList = data.data;
                return this.babList;
            } finally {
                this.loading = false;
            }
        },

        async fetchMateriList(babId) {
            const { data } = await axios.get('/api/materi', { params: { bab_id: babId } });
            return data.data;
        },

        async fetchMateri(materiId) {
            const { data } = await axios.get(`/api/materi/${materiId}`);
            this.currentMateri = data.data;
            return this.currentMateri;
        },

        async tandaiSelesai(materiId, jawaban = []) {
            const { data } = await axios.post(`/api/materi/${materiId}/selesai`, { jawaban });
            if (this.currentMateri?.id === materiId) {
                this.currentMateri.selesai = true;
            }
            return data;
        },
    },
});
