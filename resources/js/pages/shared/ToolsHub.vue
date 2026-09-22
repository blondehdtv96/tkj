<script setup>
import { ref } from 'vue';
import SubnetCalculator from '../../components/tkj/SubnetCalculator.vue';
import OsiLayerViewer from '../../components/tkj/OsiLayerViewer.vue';
import TopologyBuilder from '../../components/tkj/TopologyBuilder.vue';
import CliSimulator from '../../components/tkj/CliSimulator.vue';

const tabs = [
    { key: 'subnet', label: 'Subnet Calculator', component: SubnetCalculator },
    { key: 'osi', label: 'OSI Layer', component: OsiLayerViewer },
    { key: 'topology', label: 'Topology Builder', component: TopologyBuilder },
    { key: 'cli', label: 'CLI Simulator', component: CliSimulator },
];

const aktif = ref(tabs[0].key);
</script>

<template>
    <div class="space-y-4">
        <h1 class="text-2xl font-semibold text-primary-900 dark:text-white">Alat Bantu TKJ</h1>

        <div class="flex flex-wrap gap-2 border-b border-slate-200 dark:border-slate-700 pb-2">
            <button
                v-for="tab in tabs"
                :key="tab.key"
                class="rounded-lg px-3 py-1.5 text-sm font-medium"
                :class="aktif === tab.key
                    ? 'bg-primary-600 text-white'
                    : 'bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-600'"
                @click="aktif = tab.key"
            >
                {{ tab.label }}
            </button>
        </div>

        <component :is="tabs.find((t) => t.key === aktif).component" />
    </div>
</template>
