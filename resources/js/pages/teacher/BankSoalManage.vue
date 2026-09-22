<script setup>
import { ref, onMounted, watch } from 'vue';
import axios from 'axios';
import { useToastStore } from '../../stores/toast';
import { useConfirmStore } from '../../stores/confirm';

const toast = useToastStore();
const confirmStore = useConfirmStore();
const tingkatList = [10, 11, 12];
const tingkat = ref(10);
const mapelList = ref([]);
const mapelId = ref(null);
const babList = ref([]);
const babId = ref(null);
const kuisList = ref([]);
const kuisId = ref(null);
const soalList = ref([]);

const kosongKuis = () => ({ id: null, judul: '', durasi_menit: 30, kkm: 70, acak_soal: true, aktif: true });
const formKuis = ref(kosongKuis());
const showFormKuis = ref(false);
const savingKuis = ref(false);

const kosongOpsi = () => ({ teks: '', is_benar: false });
const kosongSoal = () => ({
    id: null,
    tipe: 'pilihan_ganda',
    pertanyaan: '',
    skenario: '',
    pembahasan: '',
    bobot: 1,
    poin: null,
    gambar: null,
    opsi_jawaban: [kosongOpsi(), kosongOpsi()],
});

const labelTipe = {
    pilihan_ganda: 'Pilihan Ganda',
    benar_salah: 'Benar / Salah',
    essay: 'Essay',
    troubleshooting: 'Penanganan Troubleshooting',
};
const formSoal = ref(kosongSoal());
const showFormSoal = ref(false);
const savingSoal = ref(false);
const errorSoal = ref('');

async function loadMapel() {
    const { data } = await axios.get('/api/mata-pelajaran', { params: { tingkat: tingkat.value } });
    mapelList.value = data.data;
    mapelId.value = mapelList.value[0]?.id ?? null;
}

async function loadBab() {
    babList.value = [];
    babId.value = null;
    if (! mapelId.value) return;
    const { data } = await axios.get('/api/bab', { params: { mata_pelajaran_id: mapelId.value } });
    babList.value = data.data;
    babId.value = babList.value[0]?.id ?? null;
}

async function loadKuis() {
    kuisList.value = [];
    kuisId.value = null;
    if (! babId.value) return;
    const { data } = await axios.get('/api/kuis', { params: { bab_id: babId.value } });
    kuisList.value = data.data;
    kuisId.value = kuisList.value[0]?.id ?? null;
}

async function loadSoal() {
    soalList.value = [];
    if (! kuisId.value) return;
    const { data } = await axios.get('/api/soal', { params: { kuis_id: kuisId.value } });
    soalList.value = data.data;
}

onMounted(loadMapel);
watch(tingkat, loadMapel);
watch(mapelId, loadBab);
watch(babId, loadKuis);
watch(kuisId, loadSoal);

function tambahKuisBaru() {
    formKuis.value = kosongKuis();
    showFormKuis.value = true;
}

function editKuis(kuis) {
    formKuis.value = {
        id: kuis.id,
        judul: kuis.judul,
        durasi_menit: kuis.durasi_menit,
        kkm: kuis.kkm,
        acak_soal: kuis.acak_soal,
        aktif: kuis.aktif,
    };
    showFormKuis.value = true;
}

async function simpanKuis() {
    savingKuis.value = true;
    try {
        const payload = { ...formKuis.value, bab_id: babId.value };
        if (formKuis.value.id) {
            await axios.put(`/api/kuis/${formKuis.value.id}`, payload);
        } else {
            await axios.post('/api/kuis', payload);
        }
        showFormKuis.value = false;
        toast.success('Kuis berhasil disimpan.');
        await loadKuis();
    } finally {
        savingKuis.value = false;
    }
}

async function hapusKuis(id) {
    if (! (await confirmStore.ask('Hapus kuis ini beserta seluruh soal di dalamnya?', { title: 'Hapus Kuis' }))) return;
    await axios.delete(`/api/kuis/${id}`);
    toast.success('Kuis berhasil dihapus.');
    await loadKuis();
}

function tambahSoalBaru() {
    formSoal.value = kosongSoal();
    errorSoal.value = '';
    showFormSoal.value = true;
}

function editSoal(soal) {
    formSoal.value = {
        id: soal.id,
        tipe: soal.tipe,
        pertanyaan: soal.pertanyaan,
        skenario: soal.skenario ?? '',
        pembahasan: soal.pembahasan ?? '',
        bobot: soal.bobot,
        poin: soal.poin,
        gambar: null,
        opsi_jawaban: soal.tipe === 'essay'
            ? []
            // Langkah troubleshooting dimuat sesuai kunci urutannya.
            : [...soal.opsi_jawaban]
                .sort((a, b) => (a.urutan ?? 0) - (b.urutan ?? 0))
                .map((o) => ({ teks: o.teks, is_benar: o.is_benar })),
    };
    errorSoal.value = '';
    showFormSoal.value = true;
}

function ubahTipeSoal() {
    const tipe = formSoal.value.tipe;

    if (tipe === 'essay') {
        formSoal.value.opsi_jawaban = [];
    } else if (tipe === 'benar_salah') {
        formSoal.value.opsi_jawaban = [
            { teks: 'Benar', is_benar: false },
            { teks: 'Salah', is_benar: false },
        ];
    } else if (! formSoal.value.opsi_jawaban.length) {
        formSoal.value.opsi_jawaban = [kosongOpsi(), kosongOpsi()];
    }

    if (tipe !== 'troubleshooting') {
        formSoal.value.skenario = '';
    }
}

function tambahOpsi() {
    formSoal.value.opsi_jawaban.push(kosongOpsi());
}

function hapusOpsi(index) {
    formSoal.value.opsi_jawaban.splice(index, 1);
}

function tandaiBenar(index) {
    formSoal.value.opsi_jawaban.forEach((o, i) => { o.is_benar = i === index; });
}

/**
 * Urutan langkah troubleshooting ditentukan oleh posisinya di daftar, jadi
 * memindahkan baris sudah cukup untuk mengubah kunci jawaban.
 */
function geserLangkah(index, arah) {
    const tujuan = index + arah;
    const daftar = formSoal.value.opsi_jawaban;
    if (tujuan < 0 || tujuan >= daftar.length) return;

    [daftar[index], daftar[tujuan]] = [daftar[tujuan], daftar[index]];
}

async function simpanSoal() {
    errorSoal.value = '';
    const tipe = formSoal.value.tipe;

    if (tipe === 'troubleshooting' && formSoal.value.opsi_jawaban.length < 2) {
        errorSoal.value = 'Soal troubleshooting membutuhkan minimal dua langkah penanganan.';
        return;
    }

    if (tipe !== 'essay' && tipe !== 'troubleshooting' && ! formSoal.value.opsi_jawaban.some((o) => o.is_benar)) {
        errorSoal.value = 'Tepat satu opsi jawaban harus ditandai sebagai benar.';
        return;
    }

    savingSoal.value = true;
    try {
        const payload = new FormData();
        payload.append('kuis_id', kuisId.value);
        payload.append('pertanyaan', formSoal.value.pertanyaan);
        payload.append('tipe', formSoal.value.tipe);
        payload.append('skenario', formSoal.value.skenario || '');
        payload.append('pembahasan', formSoal.value.pembahasan || '');
        payload.append('bobot', formSoal.value.bobot);
        if (formSoal.value.poin !== null && formSoal.value.poin !== '') {
            payload.append('poin', formSoal.value.poin);
        }
        if (formSoal.value.gambar) {
            payload.append('gambar', formSoal.value.gambar);
        }
        formSoal.value.opsi_jawaban.forEach((opsi, index) => {
            payload.append(`opsi_jawaban[${index}][teks]`, opsi.teks);
            // Semua langkah troubleshooting benar; yang dinilai adalah posisinya.
            const benar = formSoal.value.tipe === 'troubleshooting' ? true : opsi.is_benar;
            payload.append(`opsi_jawaban[${index}][is_benar]`, benar ? '1' : '0');

            if (formSoal.value.tipe === 'troubleshooting') {
                payload.append(`opsi_jawaban[${index}][urutan]`, index + 1);
            }
        });

        if (formSoal.value.id) {
            await axios.post(`/api/soal/${formSoal.value.id}`, payload);
        } else {
            await axios.post('/api/soal', payload);
        }

        showFormSoal.value = false;
        toast.success('Soal berhasil disimpan.');
        await loadSoal();
    } catch (e) {
        errorSoal.value = e.response?.data?.message ?? 'Gagal menyimpan soal.';
    } finally {
        savingSoal.value = false;
    }
}

async function hapusSoal(id) {
    if (! (await confirmStore.ask('Hapus soal ini?', { title: 'Hapus Soal' }))) return;
    await axios.delete(`/api/soal/${id}`);
    toast.success('Soal berhasil dihapus.');
    await loadSoal();
}
</script>

<template>
    <div class="space-y-4">
        <h1 class="text-2xl font-semibold text-primary-900 dark:text-white">Bank Soal & Kuis</h1>

        <div class="flex flex-wrap gap-3 items-end rounded-xl bg-white dark:bg-slate-800 p-4 shadow-sm">
            <div>
                <label class="block text-xs font-medium text-slate-500 dark:text-slate-400">Tingkat</label>
                <select v-model.number="tingkat" class="mt-1 rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-700">
                    <option v-for="t in tingkatList" :key="t" :value="t">Kelas {{ t }}</option>
                </select>
            </div>

            <div class="flex-1 min-w-[180px]">
                <label class="block text-xs font-medium text-slate-500 dark:text-slate-400">Mata Pelajaran</label>
                <select v-model.number="mapelId" class="mt-1 w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-700">
                    <option v-for="m in mapelList" :key="m.id" :value="m.id">{{ m.nama }}</option>
                </select>
            </div>

            <div class="flex-1 min-w-[180px]">
                <label class="block text-xs font-medium text-slate-500 dark:text-slate-400">Bab</label>
                <select v-model.number="babId" class="mt-1 w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-700">
                    <option v-for="b in babList" :key="b.id" :value="b.id">{{ b.urutan }}. {{ b.judul }}</option>
                </select>
                <p v-if="!babList.length" class="mt-1 text-xs text-slate-400">Belum ada bab. Buat bab di menu Materi.</p>
            </div>
        </div>

        <div v-if="babId" class="grid gap-4 lg:grid-cols-[280px_1fr]">
            <div class="rounded-xl bg-white dark:bg-slate-800 p-4 shadow-sm space-y-2">
                <div class="flex items-center justify-between">
                    <h2 class="text-sm font-semibold text-primary-900 dark:text-white">Kuis</h2>
                    <button class="text-xs text-primary-600 hover:underline" @click="tambahKuisBaru">+ Tambah</button>
                </div>

                <form v-if="showFormKuis" class="space-y-2 rounded-lg border border-slate-200 dark:border-slate-600 p-3" @submit.prevent="simpanKuis">
                    <input v-model="formKuis.judul" required placeholder="Judul kuis" class="w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-700 text-sm" />
                    <div class="flex gap-2">
                        <input v-model.number="formKuis.durasi_menit" type="number" min="1" placeholder="Durasi (menit)" class="w-1/2 rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-700 text-sm" />
                        <input v-model.number="formKuis.kkm" type="number" min="0" max="100" placeholder="KKM" class="w-1/2 rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-700 text-sm" />
                    </div>
                    <label class="flex items-center gap-2 text-xs text-slate-600 dark:text-slate-300">
                        <input v-model="formKuis.acak_soal" type="checkbox" /> Acak urutan soal
                    </label>
                    <label class="flex items-center gap-2 text-xs text-slate-600 dark:text-slate-300">
                        <input v-model="formKuis.aktif" type="checkbox" /> Aktifkan kuis
                    </label>
                    <div class="flex gap-2">
                        <button type="submit" :disabled="savingKuis" class="flex-1 rounded-lg bg-primary-600 px-3 py-1.5 text-xs text-white disabled:opacity-50">
                            {{ savingKuis ? 'Menyimpan...' : 'Simpan' }}
                        </button>
                        <button type="button" class="rounded-lg bg-slate-100 dark:bg-slate-700 px-3 py-1.5 text-xs text-slate-600 dark:text-slate-300" @click="showFormKuis = false">
                            Batal
                        </button>
                    </div>
                </form>

                <div
                    v-for="kuis in kuisList"
                    :key="kuis.id"
                    class="rounded-lg px-3 py-2 text-sm"
                    :class="kuisId === kuis.id ? 'bg-primary-600 text-white' : 'bg-slate-50 dark:bg-slate-700/50 text-slate-700 dark:text-slate-200'"
                >
                    <button class="w-full text-left flex items-center justify-between" @click="kuisId = kuis.id">
                        <span>{{ kuis.judul }}</span>
                        <span class="text-xs opacity-70">{{ kuis.jumlah_soal ?? 0 }} soal</span>
                    </button>
                    <div class="mt-1 flex gap-2 text-xs" :class="kuisId === kuis.id ? 'text-white/80' : 'text-slate-400'">
                        <span v-if="!kuis.aktif" class="rounded-full bg-black/10 px-2">nonaktif</span>
                        <button class="hover:underline" @click.stop="editKuis(kuis)">Edit</button>
                        <button class="hover:underline" @click.stop="hapusKuis(kuis.id)">Hapus</button>
                    </div>
                </div>
                <p v-if="!kuisList.length" class="text-xs text-slate-400">Belum ada kuis di bab ini.</p>
            </div>

            <div class="rounded-xl bg-white dark:bg-slate-800 p-4 shadow-sm space-y-3">
                <div class="flex items-center justify-between">
                    <h2 class="text-sm font-semibold text-primary-900 dark:text-white">Soal</h2>
                    <button v-if="kuisId" class="text-xs text-primary-600 hover:underline" @click="tambahSoalBaru">+ Tambah Soal</button>
                </div>

                <form v-if="showFormSoal" class="space-y-3 rounded-lg border border-slate-200 dark:border-slate-600 p-3" @submit.prevent="simpanSoal">
                    <p v-if="errorSoal" class="rounded-lg bg-red-50 dark:bg-red-900/40 px-3 py-2 text-xs text-red-600 dark:text-red-300">{{ errorSoal }}</p>

                    <select v-model="formSoal.tipe" class="rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-700 text-sm" @change="ubahTipeSoal">
                        <option value="pilihan_ganda">Pilihan Ganda</option>
                        <option value="benar_salah">Benar / Salah</option>
                        <option value="essay">Essay</option>
                        <option value="troubleshooting">Penanganan Troubleshooting</option>
                    </select>

                    <textarea
                        v-if="formSoal.tipe === 'troubleshooting'"
                        v-model="formSoal.skenario"
                        rows="2"
                        maxlength="2000"
                        placeholder="Skenario kasus, mis. PC baru dirakit tidak menyala sama sekali."
                        class="w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-700 text-sm"
                    />

                    <textarea v-model="formSoal.pertanyaan" required rows="3" placeholder="Pertanyaan" class="w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-700 text-sm" />

                    <!-- Langkah penanganan: posisi baris menjadi kunci jawaban -->
                    <div v-if="formSoal.tipe === 'troubleshooting'" class="space-y-2">
                        <p class="text-xs font-medium text-slate-500 dark:text-slate-400">
                            Langkah Penanganan (susun dari urutan pertama ke terakhir)
                        </p>
                        <div v-for="(opsi, index) in formSoal.opsi_jawaban" :key="index" class="flex items-center gap-2">
                            <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-primary-100 text-xs font-semibold text-primary-700 dark:bg-primary-900/40 dark:text-primary-200">
                                {{ index + 1 }}
                            </span>
                            <input
                                v-model="opsi.teks"
                                required
                                placeholder="Langkah penanganan"
                                class="flex-1 rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-700 text-sm"
                            />
                            <button
                                type="button"
                                :disabled="index === 0"
                                class="rounded px-1.5 text-slate-500 hover:bg-slate-100 disabled:opacity-30 dark:hover:bg-slate-700"
                                title="Naikkan"
                                @click="geserLangkah(index, -1)"
                            >
                                &uarr;
                            </button>
                            <button
                                type="button"
                                :disabled="index === formSoal.opsi_jawaban.length - 1"
                                class="rounded px-1.5 text-slate-500 hover:bg-slate-100 disabled:opacity-30 dark:hover:bg-slate-700"
                                title="Turunkan"
                                @click="geserLangkah(index, 1)"
                            >
                                &darr;
                            </button>
                            <button
                                v-if="formSoal.opsi_jawaban.length > 2"
                                type="button"
                                class="text-xs text-red-500 hover:underline"
                                @click="hapusOpsi(index)"
                            >
                                Hapus
                            </button>
                        </div>
                        <button type="button" class="text-xs text-primary-600 hover:underline" @click="tambahOpsi">
                            + Tambah langkah
                        </button>
                        <p class="text-xs text-slate-400">
                            Siswa menerima langkah dalam keadaan teracak dan harus menyusunnya kembali. Posisi yang tepat dinilai sebagian.
                        </p>
                    </div>

                    <div v-else-if="formSoal.tipe !== 'essay'" class="space-y-2">
                        <p class="text-xs font-medium text-slate-500 dark:text-slate-400">Opsi Jawaban (pilih satu yang benar)</p>
                        <div v-for="(opsi, index) in formSoal.opsi_jawaban" :key="index" class="flex items-center gap-2">
                            <input type="radio" name="opsi-benar" :checked="opsi.is_benar" @change="tandaiBenar(index)" />
                            <input
                                v-model="opsi.teks"
                                required
                                :readonly="formSoal.tipe === 'benar_salah'"
                                placeholder="Teks opsi"
                                class="flex-1 rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-700 text-sm"
                            />
                            <button
                                v-if="formSoal.tipe === 'pilihan_ganda' && formSoal.opsi_jawaban.length > 2"
                                type="button"
                                class="text-xs text-red-500 hover:underline"
                                @click="hapusOpsi(index)"
                            >
                                Hapus
                            </button>
                        </div>
                        <button v-if="formSoal.tipe === 'pilihan_ganda'" type="button" class="text-xs text-primary-600 hover:underline" @click="tambahOpsi">
                            + Tambah opsi
                        </button>
                    </div>

                    <div class="flex flex-wrap gap-3">
                        <label class="block">
                            <span class="block text-xs font-medium text-slate-500 dark:text-slate-400">Bobot nilai</span>
                            <input v-model.number="formSoal.bobot" type="number" min="1" class="mt-1 w-32 rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-700 text-sm" />
                        </label>
                        <label class="block">
                            <span class="block text-xs font-medium text-slate-500 dark:text-slate-400">Poin gamifikasi</span>
                            <input
                                v-model.number="formSoal.poin"
                                type="number"
                                min="0"
                                max="1000"
                                placeholder="bawaan"
                                class="mt-1 w-36 rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-700 text-sm"
                            />
                        </label>
                        <p class="w-full text-xs text-slate-400">
                            Bobot menentukan nilai kuis, poin menentukan perolehan gamifikasi. Kosongkan poin untuk memakai nilai bawaan.
                        </p>
                    </div>

                    <textarea v-model="formSoal.pembahasan" rows="2" placeholder="Pembahasan (opsional)" class="w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-700 text-sm" />

                    <div>
                        <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Gambar (opsional)</label>
                        <input type="file" accept="image/*" class="text-sm" @change="formSoal.gambar = $event.target.files[0]" />
                    </div>

                    <div class="flex gap-2">
                        <button type="submit" :disabled="savingSoal" class="rounded-lg bg-primary-600 px-4 py-1.5 text-sm text-white disabled:opacity-50">
                            {{ savingSoal ? 'Menyimpan...' : 'Simpan' }}
                        </button>
                        <button type="button" class="rounded-lg bg-slate-100 dark:bg-slate-700 px-4 py-1.5 text-sm text-slate-600 dark:text-slate-300" @click="showFormSoal = false">
                            Batal
                        </button>
                    </div>
                </form>

                <div v-for="(soal, index) in soalList" :key="soal.id" class="rounded-lg bg-slate-50 dark:bg-slate-700/50 px-3 py-2 text-sm">
                    <div class="flex items-start justify-between gap-2">
                        <p class="text-slate-700 dark:text-slate-200">
                            {{ index + 1 }}. {{ soal.pertanyaan }}
                            <span class="text-xs text-slate-400">
                                ({{ labelTipe[soal.tipe] ?? soal.tipe }}, bobot {{ soal.bobot }}, {{ soal.poin_efektif }} poin<span v-if="soal.poin === null"> bawaan</span>)
                            </span>
                        </p>
                        <div class="flex gap-2 text-xs shrink-0">
                            <button class="text-primary-600 hover:underline" @click="editSoal(soal)">Edit</button>
                            <button class="text-red-500 hover:underline" @click="hapusSoal(soal.id)">Hapus</button>
                        </div>
                    </div>
                </div>
                <p v-if="kuisId && !soalList.length" class="text-xs text-slate-400">Belum ada soal di kuis ini.</p>
                <p v-if="!kuisId" class="text-xs text-slate-400">Pilih atau buat kuis terlebih dahulu.</p>
            </div>
        </div>
        <p v-else class="text-sm text-slate-400">Pilih bab terlebih dahulu untuk mengelola kuis dan soal.</p>
    </div>
</template>
