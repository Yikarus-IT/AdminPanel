<script setup>
defineProps({
    stat: {
        type: Object,
        required: true,
    },
});

const tones = {
    emerald: 'from-emerald-500/18 to-emerald-100 text-emerald-700',
    blue: 'from-sky-500/18 to-sky-100 text-sky-700',
    amber: 'from-amber-500/18 to-amber-100 text-amber-700',
    rose: 'from-rose-500/18 to-rose-100 text-rose-700',
};
</script>

<template>
    <article class="rounded-[28px] border border-white/70 bg-white p-5 shadow-[0_24px_80px_-40px_rgba(15,23,42,0.45)]">
        <div
            class="inline-flex rounded-full bg-gradient-to-br px-3 py-1 text-xs font-semibold uppercase tracking-[0.25em]"
            :class="tones[stat.tone]"
        >
            {{ stat.change }}
        </div>
        <p class="mt-5 text-sm text-slate-500">{{ stat.title }}</p>
        <p class="mt-2 text-3xl font-semibold tracking-tight text-slate-950">{{ stat.value }}</p>
    </article>
</template>
