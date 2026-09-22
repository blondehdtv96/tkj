<script setup>
import { ref, computed } from 'vue';
import Icon from '../Icon.vue';

const paletteItems = [
    { tipe: 'pc', label: 'PC', icon: 'desktop' },
    { tipe: 'switch', label: 'Switch', icon: 'switch' },
    { tipe: 'router', label: 'Router', icon: 'router' },
    { tipe: 'server', label: 'Server', icon: 'server' },
    { tipe: 'ap', label: 'Access Point', icon: 'wifi' },
];

let nextId = 1;
const devices = ref([]);
const cables = ref([]);

const mode = ref('move'); // 'move' | 'connect'
const connectFrom = ref(null);
const dragging = ref(null);
const svgRef = ref(null);

function tambahDevice(tipe) {
    devices.value.push({
        id: nextId++,
        tipe,
        label: paletteItems.find((p) => p.tipe === tipe).label,
        x: 60 + Math.random() * 300,
        y: 60 + Math.random() * 200,
    });
}

function svgPoint(event) {
    const rect = svgRef.value.getBoundingClientRect();
    return { x: event.clientX - rect.left, y: event.clientY - rect.top };
}

function mulaiDrag(device, event) {
    if (mode.value === 'connect') {
        pilihKoneksi(device);
        return;
    }
    const point = svgPoint(event);
    dragging.value = { id: device.id, offsetX: point.x - device.x, offsetY: point.y - device.y };
}

function saatDrag(event) {
    if (! dragging.value) return;
    const point = svgPoint(event);
    const device = devices.value.find((d) => d.id === dragging.value.id);
    device.x = Math.max(30, Math.min(point.x - dragging.value.offsetX, 720));
    device.y = Math.max(30, Math.min(point.y - dragging.value.offsetY, 360));
}

function selesaiDrag() {
    dragging.value = null;
}

function pilihKoneksi(device) {
    if (! connectFrom.value) {
        connectFrom.value = device.id;
        return;
    }
    if (connectFrom.value === device.id) {
        connectFrom.value = null;
        return;
    }
    const sudahAda = cables.value.some(
        (c) => (c.from === connectFrom.value && c.to === device.id) || (c.from === device.id && c.to === connectFrom.value)
    );
    if (! sudahAda) {
        cables.value.push({ from: connectFrom.value, to: device.id });
    }
    connectFrom.value = null;
}

function hapusDevice(id) {
    devices.value = devices.value.filter((d) => d.id !== id);
    cables.value = cables.value.filter((c) => c.from !== id && c.to !== id);
}

function hapusSemua() {
    devices.value = [];
    cables.value = [];
    connectFrom.value = null;
}

function deviceById(id) {
    return devices.value.find((d) => d.id === id);
}

const validasi = computed(() => {
    const pesan = [];

    if (devices.value.length === 0) {
        return [{ ok: false, text: 'Tambahkan perangkat untuk mulai membangun topologi.' }];
    }

    const isolasi = devices.value.filter((d) => ! cables.value.some((c) => c.from === d.id || c.to === d.id));
    if (isolasi.length) {
        pesan.push(`${isolasi.length} perangkat belum terhubung: ${isolasi.map((d) => d.label).join(', ')}.`);
    }

    const pcLangsungKePc = cables.value.some((c) => deviceById(c.from)?.tipe === 'pc' && deviceById(c.to)?.tipe === 'pc');
    if (pcLangsungKePc) {
        pesan.push('PC tidak lazim dihubungkan langsung ke PC lain tanpa perangkat penghubung (switch/AP).');
    }

    const adaPenghubung = devices.value.some((d) => ['switch', 'router', 'ap'].includes(d.tipe));
    if (devices.value.some((d) => d.tipe === 'pc') && ! adaPenghubung) {
        pesan.push('Tambahkan minimal satu switch/router/access point agar PC dapat saling terhubung.');
    }

    if (cables.value.length > devices.value.length - 1) {
        pesan.push('Kemungkinan ada jalur ganda (loop) antar perangkat. Aktifkan Spanning Tree Protocol (STP) pada switch untuk mencegah broadcast storm.');
    }

    if (pesan.length === 0) {
        return [{ ok: true, text: 'Struktur topologi valid.' }];
    }

    return pesan.map((text) => ({ ok: false, text }));
});
</script>

<template>
    <div class="rounded-xl bg-white dark:bg-slate-800 p-5 shadow-sm">
        <h2 class="font-semibold text-primary-900 dark:text-white mb-3">Topology Builder</h2>

        <div class="flex flex-wrap items-center gap-2 mb-3">
            <button
                v-for="item in paletteItems"
                :key="item.tipe"
                class="flex items-center gap-1.5 rounded-lg bg-slate-100 dark:bg-slate-700 px-3 py-1.5 text-sm text-slate-700 dark:text-slate-200 hover:bg-slate-200 dark:hover:bg-slate-600"
                @click="tambahDevice(item.tipe)"
            >
                <Icon :name="item.icon" :size="16" />
                + {{ item.label }}
            </button>

            <div class="mx-2 h-6 w-px bg-slate-200 dark:bg-slate-600" />

            <button
                class="rounded-lg px-3 py-1.5 text-sm font-medium"
                :class="mode === 'move' ? 'bg-primary-600 text-white' : 'bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-200'"
                @click="mode = 'move'; connectFrom = null"
            >
                Geser
            </button>
            <button
                class="rounded-lg px-3 py-1.5 text-sm font-medium"
                :class="mode === 'connect' ? 'bg-teal-600 text-white' : 'bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-200'"
                @click="mode = 'connect'"
            >
                Hubungkan Kabel
            </button>

            <button class="ml-auto rounded-lg px-3 py-1.5 text-sm text-red-600 hover:bg-red-50 dark:hover:bg-red-900/30" @click="hapusSemua">
                Bersihkan
            </button>
        </div>

        <p v-if="mode === 'connect'" class="mb-2 text-xs text-teal-600 dark:text-teal-300">
            Klik dua perangkat berurutan untuk menghubungkannya dengan kabel.
        </p>

        <svg
            ref="svgRef"
            viewBox="0 0 760 420"
            class="w-full h-[420px] rounded-lg border border-slate-200 dark:border-slate-600 bg-slate-50 dark:bg-slate-900 touch-none"
            @pointermove="saatDrag"
            @pointerup="selesaiDrag"
            @pointerleave="selesaiDrag"
        >
            <line
                v-for="(cable, i) in cables"
                :key="i"
                :x1="deviceById(cable.from)?.x"
                :y1="deviceById(cable.from)?.y"
                :x2="deviceById(cable.to)?.x"
                :y2="deviceById(cable.to)?.y"
                stroke="#0d8272"
                stroke-width="2"
            />

            <g
                v-for="device in devices"
                :key="device.id"
                :transform="`translate(${device.x}, ${device.y})`"
                class="cursor-pointer select-none"
                @pointerdown="mulaiDrag(device, $event)"
            >
                <circle
                    r="28"
                    :fill="connectFrom === device.id ? '#0d8272' : '#ffffff'"
                    stroke="#0472d2"
                    stroke-width="2"
                    class="dark:fill-slate-700"
                />
                <g
                    class="text-primary-700 dark:text-primary-200"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.6"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                >
                    <template v-if="device.tipe === 'pc'">
                        <rect x="-9" y="-9" width="18" height="12" rx="1.2" />
                        <line x1="-4" y1="7" x2="4" y2="7" />
                        <line x1="0" y1="3" x2="0" y2="7" />
                    </template>
                    <template v-else-if="device.tipe === 'switch'">
                        <rect x="-10" y="-6" width="20" height="10" rx="1.2" />
                        <line x1="-6" y1="-6" x2="-6" y2="-3" />
                        <line x1="-2" y1="-6" x2="-2" y2="-3" />
                        <line x1="2" y1="-6" x2="2" y2="-3" />
                        <line x1="6" y1="-6" x2="6" y2="-3" />
                    </template>
                    <template v-else-if="device.tipe === 'router'">
                        <rect x="-10" y="-2" width="20" height="9" rx="1.2" />
                        <line x1="-5" y1="-2" x2="-5" y2="-8" />
                        <line x1="5" y1="-2" x2="5" y2="-8" />
                        <circle cx="-5" cy="-9" r="1" fill="currentColor" stroke="none" />
                        <circle cx="5" cy="-9" r="1" fill="currentColor" stroke="none" />
                    </template>
                    <template v-else-if="device.tipe === 'server'">
                        <rect x="-9" y="-10" width="18" height="8" rx="1" />
                        <rect x="-9" y="2" width="18" height="8" rx="1" />
                        <line x1="-6" y1="-6" x2="-3" y2="-6" />
                        <line x1="-6" y1="6" x2="-3" y2="6" />
                    </template>
                    <template v-else-if="device.tipe === 'ap'">
                        <path d="M -9 4 A 12.5 12.5 0 0 1 9 4" />
                        <path d="M -5.5 7.5 A 7 7 0 0 1 5.5 7.5" />
                        <circle cx="0" cy="10.5" r="1" fill="currentColor" stroke="none" />
                    </template>
                </g>
                <text text-anchor="middle" dy="45" font-size="11" fill="currentColor" class="text-slate-600 dark:text-slate-300 fill-current">
                    {{ device.label }}
                </text>
                <g class="cursor-pointer" @pointerdown.stop="hapusDevice(device.id)">
                    <circle cx="0" cy="-36" r="8" class="fill-white dark:fill-slate-800" stroke="#ef4444" stroke-width="1.2" />
                    <line x1="-3.5" y1="-39.5" x2="3.5" y2="-32.5" stroke="#ef4444" stroke-width="1.6" stroke-linecap="round" />
                    <line x1="3.5" y1="-39.5" x2="-3.5" y2="-32.5" stroke="#ef4444" stroke-width="1.6" stroke-linecap="round" />
                </g>
            </g>
        </svg>

        <div class="mt-3 rounded-lg bg-slate-50 dark:bg-slate-700/50 p-3 text-sm space-y-1">
            <p
                v-for="(pesan, i) in validasi"
                :key="i"
                class="flex items-center gap-1.5"
                :class="pesan.ok ? 'text-teal-600' : 'text-amber-600'"
            >
                <Icon :name="pesan.ok ? 'check-circle' : 'x-circle'" :size="15" />
                {{ pesan.text }}
            </p>
        </div>
    </div>
</template>
