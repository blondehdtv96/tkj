import { defineStore } from 'pinia';
import axios from 'axios';

export const useKuisStore = defineStore('kuis', {
    state: () => ({
        currentKuis: null,
        soalList: [],
        jawaban: {},
        hasilKuisId: null,
        waktuMulai: null,
    }),

    getters: {
        batasWaktuMs: (state) => {
            if (! state.waktuMulai || ! state.currentKuis) return null;
            return new Date(state.waktuMulai).getTime() + state.currentKuis.durasi_menit * 60 * 1000;
        },
    },

    actions: {
        async fetchDaftarKuis(babId) {
            const { data } = await axios.get('/api/kuis', { params: { bab_id: babId } });
            return data.data;
        },

        async mulaiKuis(kuisId) {
            const { data } = await axios.post(`/api/kuis/${kuisId}/mulai`);
            this.currentKuis = data.data.kuis;
            this.soalList = data.data.soal;
            this.hasilKuisId = data.data.hasil_kuis_id;
            this.waktuMulai = data.data.waktu_mulai;
            this.jawaban = {};
        },

        jawabSoal(soalId, opsiId) {
            this.jawaban[soalId] = { opsi_id: opsiId };
        },

        jawabEssay(soalId, teks) {
            this.jawaban[soalId] = { jawaban_teks: teks };
        },

        /**
         * Urutan langkah soal troubleshooting, disimpan sebagai daftar id opsi
         * dari posisi pertama sampai terakhir.
         */
        susunLangkah(soalId, urutanOpsiId) {
            this.jawaban[soalId] = { jawaban_urutan: [...urutanOpsiId] };
        },

        /**
         * Urutan kerja siswa saat ini; bila belum disentuh dipakai urutan acak
         * yang dikirim server.
         */
        urutanLangkah(soal) {
            return this.jawaban[soal.id]?.jawaban_urutan ?? soal.opsi_jawaban.map((opsi) => opsi.id);
        },

        geserLangkah(soal, index, arah) {
            const urutan = [...this.urutanLangkah(soal)];
            const tujuan = index + arah;
            if (tujuan < 0 || tujuan >= urutan.length) return;

            [urutan[index], urutan[tujuan]] = [urutan[tujuan], urutan[index]];
            this.susunLangkah(soal.id, urutan);
        },

        async submitKuis() {
            const jawaban = this.soalList.map((soal) => ({
                soal_id: soal.id,
                opsi_id: this.jawaban[soal.id]?.opsi_id ?? null,
                jawaban_teks: this.jawaban[soal.id]?.jawaban_teks ?? null,
                // Soal troubleshooting selalu mengirim urutan yang tampil di layar,
                // termasuk bila siswa membiarkannya apa adanya.
                jawaban_urutan: soal.tipe === 'troubleshooting' ? this.urutanLangkah(soal) : null,
            }));

            const { data } = await axios.post(`/api/hasil-kuis/${this.hasilKuisId}/submit`, { jawaban });

            this.currentKuis = null;
            this.soalList = [];
            this.jawaban = {};
            this.hasilKuisId = null;
            this.waktuMulai = null;

            // Blok gamifikasi dikirim di luar "data" oleh HasilKuisResource::additional().
            return { ...data.data, gamifikasi: data.gamifikasi ?? null };
        },
    },
});
