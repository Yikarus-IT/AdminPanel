<script setup>
const emit = defineEmits(['select']);

defineProps({
    item: {
        type: Object,
        required: true,
    },
});

const iconPaths = {
    grid: 'M4 4h7v7H4z M13 4h7v7h-7z M4 13h7v7H4z M13 13h7v7h-7z',
    box: 'M4 7.5 12 3l8 4.5v9L12 21l-8-4.5z M12 21v-9.5 M4 7.5l8 4 8-4',
    cart: 'M4 5h2l2.3 9.2a1 1 0 0 0 1 .8h7.8a1 1 0 0 0 1-.8L20 8H8 M10 18.5a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3z M17 18.5a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3z',
    users: 'M16 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2 M10 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8z M20 21v-2a4 4 0 0 0-3-3.87 M14 4.13a4 4 0 0 1 0 7.75',
    chart: 'M5 19V9 M12 19V5 M19 19v-7',
    settings: 'M12 2v3 M12 19v3 M4.93 4.93l2.12 2.12 M16.95 16.95l2.12 2.12 M2 12h3 M19 12h3 M4.93 19.07l2.12-2.12 M16.95 7.05l2.12-2.12 M12 16a4 4 0 1 0 0-8 4 4 0 0 0 0 8z',
};
</script>

<template>
    <button
        class="flex cursor-pointer items-center gap-3 rounded-2xl px-4 py-3 text-sm font-medium transition"
        :class="item.active ? 'bg-slate-950 text-white shadow-lg shadow-slate-950/10' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-950'"
        type="button"
        @click="emit('select', item.label)"
    >
        <svg
            class="h-5 w-5 shrink-0"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
            stroke-width="1.7"
            stroke-linecap="round"
            stroke-linejoin="round"
            aria-hidden="true"
        >
            <path :d="iconPaths[item.icon]" />
        </svg>
        <span>{{ item.label }}</span>
    </button>
</template>
