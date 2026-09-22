<script setup>
import { ref, onMounted, watch } from 'vue';
import axios from 'axios';
import RichTextEditor from '../../components/RichTextEditor.vue';
import { useConfirmStore } from '../../stores/confirm';
import Icon from '../../components/Icon.vue';

const confirmStore = useConfirmStore();
const tingkatList = [10, 11, 12];
const tingkat = ref(10);
const mapelList = ref([]);
const mapelId = ref(null);
const babList = ref([]);
const babId = ref(null);
const materiList = ref([]);

const formMapel = ref({ nama: '', tingkat: 10, deskripsi: '' });
const showFormMapel = ref(false);

const formBab = ref({ judul: '', urutan: 1 });
const showFormBab = ref(false);

const kosongMateri = () => ({ id: null, judul: '', tipe: 'teks', konten_html: '', urutan: 1, poin: null, file: null });
const formMateri = ref(kosongMateri());
const showFormMateri = ref(false);
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

async function loadMateri() {
    materiList.value = [];
    if (! babId.value) return;
    const { data } = await axios.get('/api/materi', { params: { bab_id: babId.value } });
    materiList.value = data.data;
}

onMounted(loadMapel);
watch(tingkat, loadMapel);
watch(mapelId, loadBab);
watch(babId, loadMateri);

async function simpanMapel() {
    await axios.post('/api/mata-pelajaran', { ...formMapel.value, tingkat: tingkat.value });
    showFormMapel.value = false;
    formMapel.value = { nama: '', tingkat: tingkat.value, deskripsi: '' };
    await loadMapel();
}

async function simpanBab() {
    await axios.post('/api/bab', { ...formBab.value, mata_pelajaran_id: mapelId.value });
    showFormBab.value = false;
    formBab.value = { judul: '', urutan: babList.value.length + 1 };
    await loadBab();
}

async function hapusBab(id) {
    if (! (await confirmStore.ask('Hapus bab ini beserta seluruh materi & kuis di dalamnya?', { title: 'Hapus Bab' }))) return;
    await axios.delete(`/api/bab/${id}`);
    await loadBab();
}

function editMateri(materi) {
    formMateri.value = { id: materi.id, judul: materi.judul, tipe: materi.tipe, konten_html: materi.konten_html ?? '', urutan: materi.urutan, poin: materi.poin ?? null, file: null };
    showFormMateri.value = true;
}

function tambahMateriBaru() {
    formMateri.value = { ...kosongMateri(), urutan: materiList.value.length + 1 };
    showFormMateri.value = true;
}

async function simpanMateri() {
    saving.value = true;
    try {
        const payload = new FormData();
        payload.append('bab_id', babId.value);
        payload.append('judul', formMateri.value.judul);
        payload.append('tipe', formMateri.value.tipe);
        payload.append('urutan', formMateri.value.urutan);
        if (formMateri.value.poin !== null && formMateri.value.poin !== '') {
            payload.append('poin', formMateri.value.poin);
        }
        if (formMateri.value.tipe === 'teks') {
            payload.append('konten_html', formMateri.value.konten_html || '');
        }
        if (formMateri.value.file) {
            payload.append('file', formMateri.value.file);
        }

        if (formMateri.value.id) {
            await axios.post(`/api/materi/${formMateri.value.id}`, payload);
        } else {
            await axios.post('/api/materi', payload);
        }

        showFormMateri.value = false;
        await loadMateri();
    } finally {
        saving.value = false;
    }
}

async function hapusMateri(id) {
    if (! (await confirmStore.ask('Hapus materi ini?', { title: 'Hapus Materi' }))) return;
    await axios.delete(`/api/materi/${id}`);
    await loadMateri();
}
</script>

<template>
    <div class="space-y-4">
        <h1 class="text-2xl font-semibold text-primary-900 dark:text-white">Kelola Materi</h1>

        <div class="flex flex-wrap gap-3 items-end rounded-xl bg-white dark:bg-slate-800 p-4 shadow-sm">
            <div>
                <label class="block text-xs font-medium text-slate-500 dark:text-slate-400">Tingkat</label>
                <select v-model.number="tingkat" class="mt-1 rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-700">
                    <option v-for="t in tingkatList" :key="t" :value="t">Kelas {{ t }}</option>
                </select>
            </div>

            <div class="flex-1 min-w-[200px]">
                <label class="block text-xs font-medium text-slate-500 dark:text-slate-400">Mata Pelajaran</label>
                <select v-model.number="mapelId" class="mt-1 w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-700">
                    <option v-for="m in mapelList" :key="m.id" :value="m.id">{{ m.nama }}</option>
                </select>
            </div>

            <button class="rounded-lg bg-slate-100 dark:bg-slate-700 px-3 py-2 text-sm text-slate-700 dark:text-slate-200" @click="showFormMapel = !showFormMapel">
                + Mapel Baru
            </button>
        </div>

        <form v-if="showFormMapel" class="rounded-xl bg-white dark:bg-slate-800 p-4 shadow-sm flex flex-wrap gap-2 items-end" @submit.prevent="simpanMapel">
            <input v-model="formMapel.nama" required placeholder="Nama mata pelajaran" class="rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-700 flex-1 min-w-[200px]" />
            <input v-model="formMapel.deskripsi" placeholder="Deskripsi (opsional)" class="rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-700 flex-1 min-w-[200px]" />
            <button type="submit" class="rounded-lg bg-primary-600 px-4 py-2 text-sm text-white">Simpan</button>
        </form>

        <div v-if="mapelId" class="grid gap-4 lg:grid-cols-[240px_1fr]">
            <div class="rounded-xl bg-white dark:bg-slate-800 p-4 shadow-sm space-y-2">
                <div class="flex items-center justify-between">
                    <h2 class="text-sm font-semibold text-primary-900 dark:text-white">Bab</h2>
                    <button class="text-xs text-primary-600 hover:underline" @click="showFormBab = !showFormBab">+ Tambah</button>
                </div>

                <form v-if="showFormBab" class="space-y-2" @submit.prevent="simpanBab">
                    <input v-model="formBab.judul" required placeholder="Judul bab" class="w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-700 text-sm" />
                    <input v-model.number="formBab.urutan" type="number" min="1" placeholder="Urutan" class="w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-700 text-sm" />
                    <button type="submit" class="w-full rounded-lg bg-primary-600 px-3 py-1.5 text-xs text-white">Simpan Bab</button>
                </form>

                <button
                    v-for="bab in babList"
                    :key="bab.id"
                    class="w-full flex items-center justify-between rounded-lg px-3 py-2 text-sm text-left"
                    :class="babId === bab.id ? 'bg-primary-600 text-white' : 'bg-slate-50 dark:bg-slate-700/50 text-slate-700 dark:text-slate-200'"
                    @click="babId = bab.id"
                >
                    <span>{{ bab.urutan }}. {{ bab.judul }}</span>
                    <span class="opacity-70 hover:opacity-100" @click.stop="hapusBab(bab.id)"><Icon name="close" :size="14" /></span>
                </button>
                <p v-if="!babList.length" class="text-xs text-slate-400">Belum ada bab.</p>
            </div>

            <div class="rounded-xl bg-white dark:bg-slate-800 p-4 shadow-sm space-y-3">
                <div class="flex items-center justify-between">
                    <h2 class="text-sm font-semibold text-primary-900 dark:text-white">Materi</h2>
                    <button v-if="babId" class="text-xs text-primary-600 hover:underline" @click="tambahMateriBaru">+ Tambah Materi</button>
                </div>

                <form v-if="showFormMateri" class="space-y-3 rounded-lg border border-slate-200 dark:border-slate-600 p-3" @submit.prevent="simpanMateri">
                    <input v-model="formMateri.judul" required placeholder="Judul materi" class="w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-700 text-sm" />

                    <select v-model="formMateri.tipe" class="rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-700 text-sm">
                        <option value="teks">Teks</option>
                        <option value="video">Video</option>
                        <option value="pdf">PDF</option>
                    </select>

                    <RichTextEditor v-if="formMateri.tipe === 'teks'" v-model="formMateri.konten_html" />
                    <input v-else type="file" class="text-sm" @change="formMateri.file = $event.target.files[0]" />

                    <div class="flex flex-wrap gap-3">
                        <label class="block">
                            <span class="block text-xs font-medium text-slate-500 dark:text-slate-400">Urutan</span>
                            <input v-model.number="formMateri.urutan" type="number" min="1" class="mt-1 w-24 rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-700 text-sm" />
                        </label>
                        <label class="block">
                            <span class="block text-xs font-medium text-slate-500 dark:text-slate-400">Poin gamifikasi</span>
                            <input
                                v-model.number="formMateri.poin"
                                type="number"
                                min="0"
                                max="1000"
                                placeholder="bawaan"
                                class="mt-1 w-36 rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-700 text-sm"
                            />
                        </label>
                    </div>

                    <div class="flex gap-2">
                        <button type="submit" :disabled="saving" class="rounded-lg bg-primary-600 px-4 py-1.5 text-sm text-white disabled:opacity-50">
                            {{ saving ? 'Menyimpan...' : 'Simpan' }}
                        </button>
                        <button type="button" class="rounded-lg bg-slate-100 dark:bg-slate-700 px-4 py-1.5 text-sm text-slate-600 dark:text-slate-300" @click="showFormMateri = false">
                            Batal
                        </button>
                    </div>
                </form>

                <div v-for="materi in materiList" :key="materi.id" class="flex items-center justify-between rounded-lg bg-slate-50 dark:bg-slate-700/50 px-3 py-2 text-sm">
                    <span class="text-slate-700 dark:text-slate-200">{{ materi.urutan }}. {{ materi.judul }} <span class="text-xs text-slate-400">({{ materi.tipe }})</span></span>
                    <div class="flex gap-2 text-xs">
                        <button class="text-primary-600 hover:underline" @click="editMateri(materi)">Edit</button>
                        <button class="text-red-500 hover:underline" @click="hapusMateri(materi.id)">Hapus</button>
                    </div>
                </div>
                <p v-if="babId && !materiList.length" class="text-xs text-slate-400">Belum ada materi di bab ini.</p>
                <p v-if="!babId" class="text-xs text-slate-400">Pilih atau buat bab terlebih dahulu.</p>
            </div>
        </div>
    </div>
</template>
