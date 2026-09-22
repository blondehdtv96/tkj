import axios from 'axios';
import { useToastStore } from './stores/toast';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

const token = localStorage.getItem('token');
if (token) {
    window.axios.defaults.headers.common.Authorization = `Bearer ${token}`;
}

function extractErrorMessage(error) {
    if (! error.response) {
        return 'Tidak dapat terhubung ke server. Periksa koneksi Anda.';
    }

    const { status, data } = error.response;

    if (status === 422 && data?.errors) {
        return Object.values(data.errors).flat().join(' ');
    }

    if (data?.message) {
        return data.message;
    }

    if (status === 403) {
        return 'Anda tidak memiliki akses untuk melakukan aksi ini.';
    }

    if (status >= 500) {
        return 'Terjadi kesalahan pada server. Silakan coba lagi.';
    }

    return 'Terjadi kesalahan saat memproses permintaan.';
}

window.axios.interceptors.response.use(
    (response) => response,
    (error) => {
        if (error.response?.status === 401) {
            localStorage.removeItem('token');
            localStorage.removeItem('user');
            if (window.location.pathname !== '/login') {
                window.location.href = '/login';
            }
            return Promise.reject(error);
        }

        if (error.response?.status !== 422) {
            try {
                useToastStore().error(extractErrorMessage(error));
            } catch {
                // Pinia belum aktif saat request terjadi di luar konteks aplikasi - abaikan.
            }
        }

        return Promise.reject(error);
    },
);
