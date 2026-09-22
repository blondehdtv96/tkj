<script setup>
import AppShell from './AppShell.vue';

const navItems = [
    { to: '/guru/dashboard', label: 'Dashboard', icon: 'home' },
    { to: '/guru/materi', label: 'Materi', icon: 'book' },
    { to: '/guru/bank-soal', label: 'Bank Soal', icon: 'clipboard' },
    { to: '/guru/nilai', label: 'Nilai Siswa', icon: 'chart-bar' },
    { to: '/guru/poin', label: 'Pengaturan Poin', icon: 'coin' },
    { to: '/guru/gamifikasi', label: 'Gamifikasi', icon: 'gift' },
    { to: '/guru/peringkat', label: 'Peringkat', icon: 'trophy' },
];
</script>

<template>
    <AppShell title="TKJ - Guru" :nav-items="navItems" />
</template>
