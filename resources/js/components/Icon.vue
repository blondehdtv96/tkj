<script setup>
defineProps({
    name: { type: String, required: true },
    size: { type: [Number, String], default: 20 },
});
</script>

<template>
    <svg
        :width="size"
        :height="size"
        viewBox="0 0 24 24"
        fill="none"
        stroke="currentColor"
        stroke-width="1.8"
        stroke-linecap="round"
        stroke-linejoin="round"
        aria-hidden="true"
        class="shrink-0"
    >
        <template v-if="name === 'sun'">
            <circle cx="12" cy="12" r="4" />
            <line x1="12" y1="2.5" x2="12" y2="5" />
            <line x1="12" y1="19" x2="12" y2="21.5" />
            <line x1="4.5" y1="4.5" x2="6.3" y2="6.3" />
            <line x1="17.7" y1="17.7" x2="19.5" y2="19.5" />
            <line x1="2.5" y1="12" x2="5" y2="12" />
            <line x1="19" y1="12" x2="21.5" y2="12" />
            <line x1="4.5" y1="19.5" x2="6.3" y2="17.7" />
            <line x1="17.7" y1="6.3" x2="19.5" y2="4.5" />
        </template>

        <template v-else-if="name === 'moon'">
            <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z" />
        </template>

        <template v-else-if="name === 'menu'">
            <line x1="4" y1="6" x2="20" y2="6" />
            <line x1="4" y1="12" x2="20" y2="12" />
            <line x1="4" y1="18" x2="20" y2="18" />
        </template>

        <template v-else-if="name === 'close'">
            <line x1="6" y1="6" x2="18" y2="18" />
            <line x1="18" y1="6" x2="6" y2="18" />
        </template>

        <template v-else-if="name === 'chevron-up'">
            <polyline points="6 15 12 9 18 15" />
        </template>

        <template v-else-if="name === 'chevron-down'">
            <polyline points="6 9 12 15 18 9" />
        </template>

        <template v-else-if="name === 'check'">
            <polyline points="5 13 9 17 19 7" />
        </template>

        <template v-else-if="name === 'check-circle'">
            <circle cx="12" cy="12" r="9" />
            <polyline points="8 12.5 11 15.5 16.5 9" />
        </template>

        <template v-else-if="name === 'x-circle'">
            <circle cx="12" cy="12" r="9" />
            <line x1="9" y1="9" x2="15" y2="15" />
            <line x1="15" y1="9" x2="9" y2="15" />
        </template>

        <template v-else-if="name === 'logout'">
            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" />
            <polyline points="16 17 21 12 16 7" />
            <line x1="21" y1="12" x2="9" y2="12" />
        </template>

        <template v-else-if="name === 'desktop'">
            <rect x="3" y="4" width="18" height="12" rx="1.2" />
            <line x1="8" y1="20" x2="16" y2="20" />
            <line x1="12" y1="16" x2="12" y2="20" />
        </template>

        <template v-else-if="name === 'server'">
            <rect x="3" y="4" width="18" height="6" rx="1.2" />
            <rect x="3" y="14" width="18" height="6" rx="1.2" />
            <line x1="6.5" y1="7" x2="9" y2="7" />
            <line x1="6.5" y1="17" x2="9" y2="17" />
        </template>

        <template v-else-if="name === 'router'">
            <rect x="3" y="11" width="18" height="7" rx="1.2" />
            <line x1="8" y1="11" x2="8" y2="6" />
            <line x1="16" y1="11" x2="16" y2="6" />
            <circle cx="8" cy="5" r="0.9" fill="currentColor" stroke="none" />
            <circle cx="16" cy="5" r="0.9" fill="currentColor" stroke="none" />
        </template>

        <template v-else-if="name === 'switch'">
            <rect x="3" y="8" width="18" height="8" rx="1.2" />
            <line x1="7" y1="8" x2="7" y2="10.5" />
            <line x1="11" y1="8" x2="11" y2="10.5" />
            <line x1="15" y1="8" x2="15" y2="10.5" />
            <line x1="19" y1="8" x2="19" y2="10.5" />
        </template>

        <template v-else-if="name === 'wifi'">
            <path d="M5 12.5a11 11 0 0 1 14 0" />
            <path d="M8 15.8a6.5 6.5 0 0 1 8 0" />
            <circle cx="12" cy="19" r="1" fill="currentColor" stroke="none" />
        </template>

        <template v-else-if="name === 'user'">
            <circle cx="12" cy="8" r="3.2" />
            <path d="M5 20c0-3.6 3.1-6.5 7-6.5s7 2.9 7 6.5" />
        </template>

        <template v-else-if="name === 'lock'">
            <rect x="5" y="11" width="14" height="9" rx="1.5" />
            <path d="M8 11V7a4 4 0 0 1 8 0v4" />
        </template>

        <template v-else-if="name === 'eye'">
            <path d="M2.5 12S6 5.5 12 5.5 21.5 12 21.5 12 18 18.5 12 18.5 2.5 12 2.5 12Z" />
            <circle cx="12" cy="12" r="3" />
        </template>

        <template v-else-if="name === 'eye-off'">
            <path d="M3.5 12S6.7 6 12 6c1.4 0 2.7.3 3.8.8" />
            <path d="M20.5 12s-1 2-3 3.7" />
            <path d="M9.9 9.9a3 3 0 0 0 4.2 4.2" />
            <line x1="4" y1="4" x2="20" y2="20" />
        </template>

        <template v-else-if="name === 'spinner'">
            <circle cx="12" cy="12" r="9" stroke-opacity="0.25" />
            <path d="M21 12a9 9 0 0 0-9-9" />
        </template>

        <template v-else-if="name === 'network'">
            <line x1="12" y1="6.5" x2="6" y2="16.5" />
            <line x1="12" y1="6.5" x2="18" y2="16.5" />
            <line x1="7.6" y1="17.2" x2="16.4" y2="17.2" />
            <circle cx="12" cy="6" r="2.4" fill="currentColor" stroke="none" />
            <circle cx="6" cy="17.5" r="2.4" fill="currentColor" stroke="none" />
            <circle cx="18" cy="17.5" r="2.4" fill="currentColor" stroke="none" />
        </template>

        <template v-else-if="name === 'home'">
            <path d="M4 10.5 12 4l8 6.5" />
            <path d="M6 9.5V19a1 1 0 0 0 1 1h10a1 1 0 0 0 1-1V9.5" />
            <rect x="10" y="14" width="4" height="6" />
        </template>

        <template v-else-if="name === 'book'">
            <path d="M4 5.5c2-1 4.5-1 8 0v13c-3.5-1-6-1-8 0Z" />
            <path d="M20 5.5c-2-1-4.5-1-8 0v13c3.5-1 6-1 8 0Z" />
        </template>

        <template v-else-if="name === 'clipboard'">
            <rect x="6" y="4" width="12" height="17" rx="1.5" />
            <rect x="9" y="2.5" width="6" height="3" rx="1" fill="currentColor" stroke="none" />
            <line x1="9" y1="10.5" x2="15" y2="10.5" />
            <line x1="9" y1="14" x2="15" y2="14" />
            <line x1="9" y1="17.5" x2="13" y2="17.5" />
        </template>

        <template v-else-if="name === 'chart-bar'">
            <line x1="4" y1="20" x2="20" y2="20" />
            <rect x="6" y="13" width="3" height="7" />
            <rect x="11" y="8.5" width="3" height="11.5" />
            <rect x="16" y="4.5" width="3" height="15.5" />
        </template>

        <template v-else-if="name === 'tool'">
            <g transform="rotate(45 12 12)">
                <rect x="10.5" y="3" width="3" height="8" rx="1.4" />
                <circle cx="12" cy="14.5" r="4" />
                <circle cx="12" cy="14.5" r="1.4" />
            </g>
        </template>

        <template v-else-if="name === 'users'">
            <circle cx="8.5" cy="8" r="3" />
            <path d="M3 19c0-3.3 2.5-6 5.5-6s5.5 2.7 5.5 6" />
            <circle cx="16.5" cy="8.3" r="2.3" />
            <path d="M14.7 13.1c2.4 0.5 4.1 2.8 4.3 5.4" />
        </template>

        <template v-else-if="name === 'graduation-cap'">
            <path d="M12 4 3 8.5 12 13l9-4.5Z" />
            <path d="M7 10.5V15c0 1.5 2.2 3 5 3s5-1.5 5-3v-4.5" />
            <line x1="21" y1="8.5" x2="21" y2="13.5" />
        </template>

        <template v-else-if="name === 'external-link'">
            <path d="M14 4h6v6" />
            <line x1="20" y1="4" x2="11.5" y2="12.5" />
            <path d="M18 14v5a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h5" />
        </template>

        <template v-else-if="name === 'link'">
            <g transform="rotate(45 12 12)">
                <rect x="3" y="9.3" width="8" height="5.4" rx="2.7" />
                <rect x="13" y="9.3" width="8" height="5.4" rx="2.7" />
                <line x1="9" y1="12" x2="15" y2="12" />
            </g>
        </template>

        <template v-else-if="name === 'video'">
            <rect x="3" y="6" width="13" height="12" rx="1.4" />
            <path d="M16 10.5 21 8v8l-5-2.5Z" />
        </template>

        <template v-else-if="name === 'briefcase'">
            <rect x="3" y="7.5" width="18" height="12" rx="1.4" />
            <path d="M9 7.5V6a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v1.5" />
            <line x1="3" y1="12.5" x2="21" y2="12.5" />
        </template>

        <template v-else-if="name === 'trophy'">
            <path d="M8 4h8v5a4 4 0 0 1-8 0V4Z" />
            <path d="M8 5.5H5.5V7a3 3 0 0 0 3 3" />
            <path d="M16 5.5h2.5V7a3 3 0 0 1-3 3" />
            <line x1="12" y1="13" x2="12" y2="17" />
            <path d="M8.5 20h7l-.7-3h-5.6Z" />
        </template>

        <template v-else-if="name === 'gift'">
            <rect x="3.5" y="10" width="17" height="10" rx="1.4" />
            <line x1="3.5" y1="7" x2="20.5" y2="7" />
            <rect x="3.5" y="6.5" width="17" height="3.5" rx="1" />
            <line x1="12" y1="6.5" x2="12" y2="20" />
            <path d="M12 6.5S10.8 3.5 9 3.5a2 2 0 0 0 0 3Z" />
            <path d="M12 6.5s1.2-3 3-3a2 2 0 0 1 0 3Z" />
        </template>

        <template v-else-if="name === 'star'">
            <path d="m12 3.8 2.5 5.1 5.6.8-4 3.9 1 5.6-5.1-2.7-5.1 2.7 1-5.6-4-3.9 5.6-.8Z" />
        </template>

        <template v-else-if="name === 'flame'">
            <path d="M12 3s5 4.2 5 8.6a5 5 0 0 1-10 0C7 9.3 9 7.6 9 7.6s.4 2 1.6 2.6C10.3 8 12 6 12 3Z" />
        </template>

        <template v-else-if="name === 'award'">
            <circle cx="12" cy="9.5" r="5" />
            <path d="m9 14.2-1.4 6L12 18l4.4 2.2-1.4-6" />
        </template>

        <template v-else-if="name === 'coin'">
            <circle cx="12" cy="12" r="8.2" />
            <path d="M14.3 9.4c-.5-.8-1.4-1.2-2.4-1.2-1.4 0-2.3.7-2.3 1.7 0 2.4 4.9 1.1 4.9 3.6 0 1.1-1 1.8-2.5 1.8-1.1 0-2.1-.4-2.6-1.3" />
            <line x1="12" y1="6.6" x2="12" y2="17.4" />
        </template>
    </svg>
</template>
