<script setup>
import AppShell from './AppShell.vue';

const navItems = [
    { to: '/siswa/dashboard', label: 'Dashboard', icon: 'home' },
    { to: '/siswa/materi', label: 'Materi', icon: 'book' },
    { to: '/siswa/kuis', label: 'Kuis', icon: 'clipboard' },
    { to: '/siswa/nilai', label: 'Nilai', icon: 'chart-bar' },
    { to: '/siswa/poin', label: 'Poin Saya', icon: 'coin' },
    { to: '/siswa/reward', label: 'Tukar Poin', icon: 'gift' },
    { to: '/siswa/peringkat', label: 'Peringkat', icon: 'trophy' },
    { to: '/siswa/tools', label: 'Alat Bantu', icon: 'tool' },
];
</script>

<template>
    <AppShell title="TKJ - Siswa" :nav-items="navItems" />
</template>
