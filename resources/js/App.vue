<script setup>
import { ref } from 'vue';
import DashboardSection from './components/DashboardSection.vue';
import PlaceholderSection from './components/PlaceholderSection.vue';
import ProductsSection from './components/ProductsSection.vue';
import SidebarNavItem from './components/SidebarNavItem.vue';
import UsersSection from './components/UsersSection.vue';

const navigationItems = [
    { label: 'Dashboard', icon: 'grid' },
    { label: 'Products', icon: 'box' },
    { label: 'Orders', icon: 'cart' },
    { label: 'Users', icon: 'users' },
    { label: 'Analytics', icon: 'chart' },
    { label: 'Settings', icon: 'settings' },
];

const activeSection = ref('Dashboard');

function asCurrency(value) {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }).format(Number(value || 0));
}

function openSection(label) {
    activeSection.value = label;
}
</script>

<template>
    <div class="min-h-screen px-3 py-3 text-slate-900 sm:px-4 sm:py-4">
        <div class="grid min-h-[calc(100vh-1.5rem)] grid-cols-1 gap-3 xl:grid-cols-[260px_minmax(0,1fr)_380px]">
            <aside class="admin-card overflow-hidden xl:h-full">
                <div class="border-b border-white/60 px-6 py-6">
                    <p class="font-display text-xs font-semibold uppercase tracking-[0.35em] text-slate-500">AdminPanel</p>
                    <div class="mt-4">
                        <h1 class="font-display text-2xl font-bold text-slate-950">Commerce Hub</h1>
                        <p class="mt-2 text-sm leading-6 text-slate-600">
                            A starter dashboard built with Laravel and Vue for your portfolio projects.
                        </p>
                    </div>
                </div>

                <nav class="space-y-2 px-4 py-5">
                    <SidebarNavItem
                        v-for="item in navigationItems"
                        :key="item.label"
                        :item="{ ...item, active: activeSection === item.label }"
                        @select="openSection"
                    />
                </nav>

                <div class="mx-4 mt-6 rounded-3xl bg-slate-950 px-5 py-5 text-slate-50">
                    <p class="text-xs uppercase tracking-[0.3em] text-slate-400">This week</p>
                    <p class="mt-3 text-3xl font-semibold">{{ asCurrency(8400) }}</p>
                    <p class="mt-2 text-sm leading-6 text-slate-300">Revenue is pacing ahead of last week with stronger bundle sales.</p>
                </div>
            </aside>

            <DashboardSection
                v-if="activeSection === 'Dashboard'"
                @navigate="openSection"
            />
            <ProductsSection v-else-if="activeSection === 'Products'" />
            <UsersSection v-else-if="activeSection === 'Users'" />
            <PlaceholderSection
                v-else
                :section="activeSection"
            />
        </div>
    </div>
</template>
