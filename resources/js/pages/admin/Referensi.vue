<script setup>
import { ref, onMounted, watch } from 'vue';
import axios from 'axios';
import { useToastStore } from '../../stores/toast';
import { useConfirmStore } from '../../stores/confirm';
import Icon from '../../components/Icon.vue';

const toast = useToastStore();
const confirmStore = useConfirmStore();

const tingkatList = [10, 11, 12];
const tingkat = ref(10);
const mapelList = ref([]);
const mapelId = ref(null);
const babList = ref([]);
const babId = ref(null);

const kosongForm = () => ({ ringkasan: '', video_judul: '', video_url: '', kasus_soal: '' });
const form = ref(kosongForm());
const referensiId = ref(null);
const loading = ref(false);
const saving = ref(false);

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

async function loadReferensi() {
    form.value = kosongForm();
    referensiId.value = null;
    if (! babId.value) return;

    loading.value = true;
    try {
        const { data } = await axios.get('/api/referensi-belajar', { params: { bab_id: babId.value } });
        const referensi = data.data[0];
        if (referensi) {
            referensiId.value = referensi.id;
            form.value = {
                ringkasan: referensi.ringkasan ?? '',
                video_judul: referensi.video_judul ?? '',
                video_url: referensi.video_url ?? '',
                kasus_soal: referensi.kasus_soal ?? '',
            };
        }
    } finally {
        loading.value = false;
    }
}

onMounted(loadMapel);
watch(tingkat, loadMapel);
watch(mapelId, loadBab);
watch(babId, loadReferensi);

async function simpan() {
    saving.value = true;
    try {
        const { data } = await axios.post('/api/referensi-belajar', { ...form.value, bab_id: babId.value });
        referensiId.value = data.data.id;
        toast.success('Referensi belajar berhasil disimpan.');
    } finally {
        saving.value = false;
    }
}

async function hapus() {
    if (! (await confirmStore.ask('Hapus seluruh referensi belajar untuk bab ini?', { title: 'Hapus Referensi' }))) return;
    await axios.delete(`/api/referensi-belajar/${referensiId.value}`);
    toast.success('Referensi belajar berhasil dihapus.');
    form.value = kosongForm();
    referensiId.value = null;
}
</script>

<template>
    <div class="space-y-4">
        <h1 class="text-2xl font-semibold text-primary-900 dark:text-white">Referensi Belajar</h1>
        <p class="text-sm text-slate-500 dark:text-slate-400">
            Lengkapi setiap bab dengan ringkasan materi, video pembelajaran, dan studi kasus. Semua ditampilkan langsung di halaman siswa tanpa perlu mengunduh berkas.
        </p>

        <div class="flex flex-wrap gap-3 items-end rounded-xl bg-white dark:bg-slate-800 p-4 shadow-sm">
            <div>
                <label class="block text-xs font-medium text-slate-500 dark:text-slate-400">Tingkat</label>
                <select v-model.number="tingkat" class="mt-1 rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-700">
                    <option v-for="t in tingkatList" :key="t" :value="t">Kelas {{ t }}</option>
                </select>
            </div>

            <div class="flex-1 min-w-[220px]">
                <label class="block text-xs font-medium text-slate-500 dark:text-slate-400">Mata Pelajaran</label>
                <select v-model.number="mapelId" class="mt-1 w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-700">
                    <option v-for="m in mapelList" :key="m.id" :value="m.id">{{ m.nama }}</option>
                </select>
            </div>

            <div class="flex-1 min-w-[220px]">
                <label class="block text-xs font-medium text-slate-500 dark:text-slate-400">Bab</label>
                <select v-model.number="babId" class="mt-1 w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-700">
                    <option v-for="b in babList" :key="b.id" :value="b.id">{{ b.urutan }}. {{ b.judul }}</option>
                </select>
            </div>
        </div>

        <form v-if="babId && !loading" class="space-y-5 rounded-xl bg-white dark:bg-slate-800 p-5 shadow-sm" @submit.prevent="simpan">
            <div>
                <h2 class="mb-1 flex items-center gap-2 text-sm font-semibold text-primary-900 dark:text-white">
                    <Icon name="book" :size="16" class="text-primary-500" />
                    Ringkasan Materi
                </h2>
                <p class="mb-2 text-xs text-slate-400">
                    Isi materi ditampilkan langsung di aplikasi, siswa tidak perlu membuka atau mengunduh file dari luar.
                    Gunakan tag HTML sederhana: <code>&lt;h3&gt;</code>, <code>&lt;p&gt;</code>, <code>&lt;ul&gt;/&lt;li&gt;</code>, <code>&lt;table&gt;</code>, <code>&lt;pre&gt;&lt;code&gt;</code>.
                </p>
                <div class="grid gap-3 lg:grid-cols-2">
                    <textarea
                        v-model="form.ringkasan"
                        rows="18"
                        spellcheck="false"
                        placeholder="&lt;h3&gt;Judul Bagian&lt;/h3&gt;&#10;&lt;p&gt;Penjelasan materi...&lt;/p&gt;"
                        class="w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-700 font-mono text-xs"
                    />
                    <div class="max-h-[28rem] overflow-y-auto rounded-lg border border-slate-200 dark:border-slate-600 bg-slate-50 dark:bg-slate-900 p-4">
                        <p class="mb-2 text-xs font-semibold uppercase text-slate-400">Pratinjau Tampilan Siswa</p>
                        <div
                            v-if="form.ringkasan"
                            class="prose prose-sm max-w-none dark:prose-invert prose-headings:text-primary-900 dark:prose-headings:text-white prose-table:text-xs"
                            v-html="form.ringkasan"
                        />
                        <p v-else class="text-xs text-slate-400">Belum ada isi materi.</p>
                    </div>
                </div>
            </div>

            <div>
                <h2 class="mb-1 flex items-center gap-2 text-sm font-semibold text-primary-900 dark:text-white">
                    <Icon name="video" :size="16" class="text-primary-500" />
                    Video Pembelajaran
                </h2>
                <p class="mb-2 text-xs text-slate-400">Tautan YouTube akan otomatis diputar menyatu di halaman siswa (tanpa berpindah ke situs lain).</p>
                <div class="grid gap-2 sm:grid-cols-2">
                    <input v-model="form.video_judul" placeholder="Judul video" class="rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-700 text-sm" />
                    <input v-model="form.video_url" type="url" placeholder="https://youtube.com/watch?v=..." class="rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-700 text-sm" />
                </div>
            </div>

            <div>
                <h2 class="mb-2 flex items-center gap-2 text-sm font-semibold text-primary-900 dark:text-white">
                    <Icon name="briefcase" :size="16" class="text-primary-500" />
                    Studi Kasus / Soal Analisis
                </h2>
                <textarea
                    v-model="form.kasus_soal"
                    rows="4"
                    placeholder="Tuliskan skenario studi kasus untuk melatih penalaran siswa pada topik ini..."
                    class="w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-700 text-sm"
                />
            </div>

            <div class="flex gap-2">
                <button type="submit" :disabled="saving" class="rounded-lg bg-primary-600 px-4 py-2 text-sm text-white hover:bg-primary-700 disabled:opacity-50">
                    {{ saving ? 'Menyimpan...' : 'Simpan Referensi' }}
                </button>
                <button v-if="referensiId" type="button" class="rounded-lg bg-red-50 dark:bg-red-900/30 px-4 py-2 text-sm text-red-600 dark:text-red-300 hover:bg-red-100" @click="hapus">
                    Hapus
                </button>
            </div>
        </form>

        <p v-else-if="!babList.length" class="text-sm text-slate-400">Belum ada bab untuk mata pelajaran ini.</p>
    </div>
</template>
