<script setup>
import { RouterView } from 'vue-router';
import ToastContainer from './components/ToastContainer.vue';
import ConfirmModal from './components/ConfirmModal.vue';
import PoinPopup from './components/gamifikasi/PoinPopup.vue';
</script>

<template>
    <RouterView />
    <ToastContainer />
    <ConfirmModal />
    <PoinPopup />
</template>
