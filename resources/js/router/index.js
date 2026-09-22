import { createRouter, createWebHistory } from 'vue-router';
import { useAuthStore } from '../stores/auth';

const routes = [
    {
        path: '/login',
        component: () => import('../layouts/AuthLayout.vue'),
        children: [
            { path: '', name: 'login', component: () => import('../pages/auth/Login.vue') },
        ],
    },
    {
        path: '/siswa',
        component: () => import('../layouts/StudentLayout.vue'),
        meta: { roles: ['siswa'] },
        children: [
            { path: 'dashboard', name: 'siswa.dashboard', component: () => import('../pages/student/Dashboard.vue') },
            {
                path: 'materi',
                name: 'siswa.materi',
                component: () => import('../pages/student/MataPelajaranList.vue'),
                meta: { heading: 'Materi Pembelajaran' },
            },
            {
                path: 'materi/:mataPelajaranId',
                name: 'siswa.materi.bab',
                component: () => import('../pages/student/MataPelajaranDetail.vue'),
            },
            {
                path: 'materi/detail/:materiId',
                name: 'siswa.materi.detail',
                component: () => import('../pages/student/MateriViewer.vue'),
            },
            {
                path: 'kuis',
                name: 'siswa.kuis',
                component: () => import('../pages/student/MataPelajaranList.vue'),
                meta: { heading: 'Pilih Mata Pelajaran untuk Kuis' },
            },
            {
                path: 'kuis/:kuisId/kerjakan',
                name: 'siswa.kuis.kerjakan',
                component: () => import('../pages/student/KuisAttempt.vue'),
            },
            {
                path: 'kuis/hasil/:hasilKuisId',
                name: 'siswa.kuis.hasil',
                component: () => import('../pages/student/KuisResult.vue'),
            },
            { path: 'nilai', name: 'siswa.nilai', component: () => import('../pages/student/NilaiList.vue') },
            { path: 'poin', name: 'siswa.poin', component: () => import('../pages/student/PoinDashboard.vue') },
            { path: 'reward', name: 'siswa.reward', component: () => import('../pages/student/RewardKatalog.vue') },
            { path: 'peringkat', name: 'siswa.peringkat', component: () => import('../pages/shared/Leaderboard.vue') },
            { path: 'tools', name: 'siswa.tools', component: () => import('../pages/shared/ToolsHub.vue') },
        ],
    },
    {
        path: '/guru',
        component: () => import('../layouts/TeacherLayout.vue'),
        meta: { roles: ['guru'] },
        children: [
            { path: 'dashboard', name: 'guru.dashboard', component: () => import('../pages/teacher/Dashboard.vue') },
            { path: 'materi', name: 'guru.materi', component: () => import('../pages/teacher/MateriManage.vue') },
            { path: 'bank-soal', name: 'guru.bank-soal', component: () => import('../pages/teacher/BankSoalManage.vue') },
            { path: 'nilai', name: 'guru.nilai', component: () => import('../pages/teacher/NilaiSiswa.vue') },
            { path: 'poin', name: 'guru.poin', component: () => import('../pages/shared/PoinKonten.vue') },
            { path: 'gamifikasi', name: 'guru.gamifikasi', component: () => import('../pages/shared/GamifikasiManage.vue') },
            { path: 'peringkat', name: 'guru.peringkat', component: () => import('../pages/shared/Leaderboard.vue') },
        ],
    },
    {
        path: '/admin',
        component: () => import('../layouts/AdminLayout.vue'),
        meta: { roles: ['admin'] },
        children: [
            { path: 'dashboard', name: 'admin.dashboard', component: () => import('../pages/admin/Dashboard.vue') },
            { path: 'users', name: 'admin.users', component: () => import('../pages/admin/Users.vue') },
            { path: 'kelas', name: 'admin.kelas', component: () => import('../pages/admin/Kelas.vue') },
            { path: 'mata-pelajaran', name: 'admin.mata-pelajaran', component: () => import('../pages/admin/MataPelajaran.vue') },
            { path: 'referensi', name: 'admin.referensi', component: () => import('../pages/admin/Referensi.vue') },
            { path: 'materi', name: 'admin.materi', component: () => import('../pages/teacher/MateriManage.vue') },
            { path: 'bank-soal', name: 'admin.bank-soal', component: () => import('../pages/teacher/BankSoalManage.vue') },
            { path: 'poin', name: 'admin.poin', component: () => import('../pages/shared/PoinKonten.vue') },
            { path: 'gamifikasi', name: 'admin.gamifikasi', component: () => import('../pages/shared/GamifikasiManage.vue') },
            { path: 'peringkat', name: 'admin.peringkat', component: () => import('../pages/shared/Leaderboard.vue') },
            { path: 'laporan', name: 'admin.laporan', component: () => import('../pages/admin/Laporan.vue') },
        ],
    },
    { path: '/', redirect: '/login' },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

const roleHome = {
    admin: '/admin/dashboard',
    guru: '/guru/dashboard',
    siswa: '/siswa/dashboard',
};

router.beforeEach((to) => {
    const auth = useAuthStore();
    const requiredRoles = to.matched.flatMap((record) => record.meta.roles ?? []);

    if (requiredRoles.length && !auth.isAuthenticated) {
        return { name: 'login' };
    }

    if (requiredRoles.length && !requiredRoles.includes(auth.role)) {
        return roleHome[auth.role] ?? '/login';
    }

    if (to.name === 'login' && auth.isAuthenticated) {
        return roleHome[auth.role] ?? '/login';
    }

    return true;
});

export default router;
