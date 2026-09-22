<script setup>
import { ref, onMounted, watch } from 'vue';
import axios from 'axios';
import { useToastStore } from '../../stores/toast';
import { useConfirmStore } from '../../stores/confirm';

const toast = useToastStore();
const confirmStore = useConfirmStore();
const userList = ref([]);
const kelasList = ref([]);
const loading = ref(true);
const saving = ref(false);
const error = ref('');

const roleFilter = ref('');
const search = ref('');

const kosongForm = () => ({ id: null, name: '', email: '', password: '', role: 'siswa', username: '', nis: '', nip: '', kelas_id: '' });
const form = ref(kosongForm());
const showForm = ref(false);

async function loadKelas() {
    const { data } = await axios.get('/api/kelas');
    kelasList.value = data.data;
}

async function loadUsers() {
    loading.value = true;
    try {
        const { data } = await axios.get('/api/users', {
            params: { role: roleFilter.value || undefined, search: search.value || undefined },
        });
        userList.value = data.data;
    } finally {
        loading.value = false;
    }
}

onMounted(async () => {
    await loadKelas();
    await loadUsers();
});
watch([roleFilter, search], loadUsers);

function tambahBaru() {
    form.value = kosongForm();
    error.value = '';
    showForm.value = true;
}

function editUser(user) {
    form.value = {
        id: user.id,
        name: user.name,
        email: user.email,
        password: '',
        role: user.role,
        username: user.username ?? '',
        nis: user.nis ?? '',
        nip: user.nip ?? '',
        kelas_id: user.kelas_id ?? '',
    };
    error.value = '';
    showForm.value = true;
}

async function simpanUser() {
    saving.value = true;
    error.value = '';
    try {
        const payload = { ...form.value, kelas_id: form.value.kelas_id || null };
        if (! payload.password) delete payload.password;

        if (form.value.id) {
            await axios.put(`/api/users/${form.value.id}`, payload);
        } else {
            await axios.post('/api/users', payload);
        }
        showForm.value = false;
        toast.success('Pengguna berhasil disimpan.');
        await loadUsers();
    } catch (e) {
        error.value = Object.values(e.response?.data?.errors ?? {}).flat().join(' ') || 'Gagal menyimpan pengguna.';
    } finally {
        saving.value = false;
    }
}

async function hapusUser(id) {
    if (! (await confirmStore.ask('Hapus pengguna ini?', { title: 'Hapus Pengguna' }))) return;
    await axios.delete(`/api/users/${id}`);
    toast.success('Pengguna berhasil dihapus.');
    await loadUsers();
}
</script>

<template>
    <div class="space-y-4">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h1 class="text-2xl font-semibold text-primary-900 dark:text-white">Kelola Pengguna</h1>
            <button class="rounded-lg bg-primary-600 px-4 py-2 text-sm font-medium text-white hover:bg-primary-700" @click="tambahBaru">
                + Pengguna Baru
            </button>
        </div>

        <div class="flex flex-wrap gap-3 items-end rounded-xl bg-white dark:bg-slate-800 p-4 shadow-sm">
            <div>
                <label class="block text-xs font-medium text-slate-500 dark:text-slate-400">Role</label>
                <select v-model="roleFilter" class="mt-1 rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-700">
                    <option value="">Semua Role</option>
                    <option value="admin">Admin</option>
                    <option value="guru">Guru</option>
                    <option value="siswa">Siswa</option>
                </select>
            </div>
            <div class="flex-1 min-w-[200px]">
                <label class="block text-xs font-medium text-slate-500 dark:text-slate-400">Cari</label>
                <input v-model="search" placeholder="Nama, NIS, atau username" class="mt-1 w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-700" />
            </div>
        </div>

        <form v-if="showForm" class="space-y-3 rounded-xl bg-white dark:bg-slate-800 p-4 shadow-sm" @submit.prevent="simpanUser">
            <p v-if="error" class="rounded-lg bg-red-50 dark:bg-red-900/40 px-3 py-2 text-xs text-red-600 dark:text-red-300">{{ error }}</p>
            <div class="grid gap-3 sm:grid-cols-2">
                <input v-model="form.name" required placeholder="Nama lengkap" class="rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-700 text-sm" />
                <input v-model="form.email" type="email" required placeholder="Email" class="rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-700 text-sm" />
                <input v-model="form.password" type="password" :placeholder="form.id ? 'Password (kosongkan jika tidak diubah)' : 'Password'" class="rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-700 text-sm" />
                <select v-model="form.role" class="rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-700 text-sm">
                    <option value="admin">Admin</option>
                    <option value="guru">Guru</option>
                    <option value="siswa">Siswa</option>
                </select>
                <input v-model="form.username" placeholder="Username (opsional)" class="rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-700 text-sm" />
                <select v-model="form.kelas_id" class="rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-700 text-sm">
                    <option value="">Tanpa Kelas</option>
                    <option v-for="k in kelasList" :key="k.id" :value="k.id">{{ k.nama_rombel }}</option>
                </select>
                <input v-if="form.role === 'siswa'" v-model="form.nis" placeholder="NIS" class="rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-700 text-sm" />
                <input v-if="form.role !== 'siswa'" v-model="form.nip" placeholder="NIP" class="rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-700 text-sm" />
            </div>
            <div class="flex gap-2">
                <button type="submit" :disabled="saving" class="rounded-lg bg-primary-600 px-4 py-2 text-sm text-white disabled:opacity-50">
                    {{ saving ? 'Menyimpan...' : 'Simpan' }}
                </button>
                <button type="button" class="rounded-lg bg-slate-100 dark:bg-slate-700 px-4 py-2 text-sm text-slate-600 dark:text-slate-300" @click="showForm = false">
                    Batal
                </button>
            </div>
        </form>

        <div class="overflow-x-auto rounded-xl bg-white dark:bg-slate-800 shadow-sm">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50 dark:bg-slate-700/50 text-slate-500 dark:text-slate-300">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium">Nama</th>
                        <th class="px-4 py-3 text-left font-medium">Email</th>
                        <th class="px-4 py-3 text-left font-medium">Role</th>
                        <th class="px-4 py-3 text-left font-medium">Kelas</th>
                        <th class="px-4 py-3 text-left font-medium">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                    <tr v-for="user in userList" :key="user.id">
                        <td class="px-4 py-3 text-slate-700 dark:text-slate-200">{{ user.name }}</td>
                        <td class="px-4 py-3 text-slate-500 dark:text-slate-400">{{ user.email }}</td>
                        <td class="px-4 py-3 capitalize text-slate-500 dark:text-slate-400">{{ user.role }}</td>
                        <td class="px-4 py-3 text-slate-500 dark:text-slate-400">{{ user.kelas?.nama_rombel ?? '-' }}</td>
                        <td class="px-4 py-3">
                            <div class="flex gap-2 text-xs">
                                <button class="text-primary-600 hover:underline" @click="editUser(user)">Edit</button>
                                <button class="text-red-500 hover:underline" @click="hapusUser(user.id)">Hapus</button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
            <p v-if="!loading && !userList.length" class="px-4 py-6 text-sm text-slate-400">Belum ada pengguna untuk filter ini.</p>
        </div>
    </div>
</template>
