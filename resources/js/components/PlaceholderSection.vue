<script setup>
defineProps({
    section: {
        type: String,
        required: true,
    },
});
</script>

<template>
    <main class="xl:col-start-2 xl:col-span-2">
        <section class="admin-card px-6 py-10">
            <p class="text-xs font-semibold uppercase tracking-[0.35em] text-slate-500">{{ section }}</p>
            <h2 class="mt-3 font-display text-3xl font-bold text-slate-950">Module placeholder</h2>
            <p class="mt-4 max-w-2xl text-sm leading-7 text-slate-600">
                This section is ready for expansion. Once products is finished, we can use the same Laravel + Vue pattern
                for orders, customers, analytics, or role-based admin features.
            </p>
        </section>
    </main>
</template>
