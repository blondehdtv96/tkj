<script setup>
import AppShell from './AppShell.vue';

const navItems = [
    { to: '/admin/dashboard', label: 'Dashboard', icon: 'home' },
    { to: '/admin/users', label: 'Pengguna', icon: 'users' },
    { to: '/admin/kelas', label: 'Kelas', icon: 'graduation-cap' },
    { to: '/admin/mata-pelajaran', label: 'Mata Pelajaran', icon: 'book' },
    { to: '/admin/referensi', label: 'Referensi Belajar', icon: 'link' },
    { to: '/admin/materi', label: 'Materi', icon: 'video' },
    { to: '/admin/bank-soal', label: 'Bank Soal', icon: 'clipboard' },
    { to: '/admin/poin', label: 'Pengaturan Poin', icon: 'coin' },
    { to: '/admin/gamifikasi', label: 'Gamifikasi', icon: 'gift' },
    { to: '/admin/peringkat', label: 'Peringkat', icon: 'trophy' },
    { to: '/admin/laporan', label: 'Laporan', icon: 'chart-bar' },
];
</script>

<template>
    <AppShell title="TKJ - Admin" :nav-items="navItems" />
</template>
