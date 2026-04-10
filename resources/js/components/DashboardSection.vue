<script setup>
import { computed, onMounted, ref } from 'vue';
import DashboardStatCard from './DashboardStatCard.vue';
import { asCurrency } from './formatters';

const emit = defineEmits(['navigate']);

const products = ref([]);

const activities = [
    { title: 'Andrea posted a new shipment update', meta: '3 minutes ago', accent: 'bg-emerald-400' },
    { title: 'Quarterly report exported to finance', meta: '18 minutes ago', accent: 'bg-sky-400' },
    { title: 'Inventory alert triggered for Standing Desk', meta: '42 minutes ago', accent: 'bg-amber-400' },
    { title: 'Customer refund approved by support', meta: '1 hour ago', accent: 'bg-rose-400' },
];

const stats = computed(() => {
    const inventoryValue = products.value.reduce((total, product) => {
        return total + Number(product.price) * Number(product.stock);
    }, 0);

    const urgentCount = products.value.filter((product) => product.status === 'Urgent').length;
    const lowStockCount = products.value.filter((product) => product.status === 'Low stock').length;

    return [
        { title: 'Products tracked', value: `${products.value.length}`, change: products.value.length ? 'Live data' : 'No items', tone: 'emerald' },
        { title: 'Inventory value', value: asCurrency(inventoryValue), change: 'Seeded demo', tone: 'blue' },
        { title: 'Low stock items', value: `${lowStockCount}`, change: lowStockCount ? 'Needs review' : 'All healthy', tone: 'amber' },
        { title: 'Urgent alerts', value: `${urgentCount}`, change: urgentCount ? 'Act now' : 'Under control', tone: 'rose' },
    ];
});

const topProducts = computed(() => products.value.slice(0, 4));

async function fetchProducts() {
    try {
        const response = await window.axios.get('/products');
        products.value = response.data.data;
    } catch {
        products.value = [];
    }
}

onMounted(fetchProducts);
</script>

<template>
    <main class="space-y-3 xl:col-start-2 xl:min-h-0">
        <section class="admin-card px-5 py-5 sm:px-6">
            <div class="flex flex-col gap-5 xl:flex-row xl:items-start xl:justify-between">
                <div class="max-w-2xl">
                    <p class="text-xs font-semibold uppercase tracking-[0.35em] text-slate-500">Dashboard overview</p>
                    <h2 class="mt-3 font-display text-3xl font-bold tracking-tight text-slate-950 sm:text-4xl">
                        Modern admin experience for Laravel + Vue.
                    </h2>
                    <p class="mt-4 max-w-xl text-sm leading-7 text-slate-600 sm:text-base">
                        This first screen gives your portfolio a professional base: KPI cards,
                        inventory table, activity feed, and a responsive layout you can reuse as the project grows.
                    </p>
                </div>

                <div class="grid gap-3 sm:grid-cols-2">
                    <button
                        class="rounded-full bg-slate-950 px-5 py-3 text-sm font-medium text-white transition hover:bg-slate-800"
                        type="button"
                        @click="emit('navigate', 'Products')"
                    >
                        New product
                    </button>
                    <button class="rounded-full border border-slate-300 bg-white px-5 py-3 text-sm font-medium text-slate-700 transition hover:border-slate-400 hover:text-slate-950">
                        Export report
                    </button>
                </div>
            </div>

            <div class="mt-8 grid gap-4 md:grid-cols-2 2xl:grid-cols-4">
                <DashboardStatCard
                    v-for="stat in stats"
                    :key="stat.title"
                    :stat="stat"
                />
            </div>
        </section>

        <section class="admin-card px-5 py-5 sm:px-6">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-500">Inventory snapshot</p>
                    <h3 class="mt-2 text-xl font-semibold text-slate-950">Top products</h3>
                </div>
                <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-600">Updated live</span>
            </div>

            <div class="mt-6 overflow-hidden rounded-3xl border border-slate-200/80">
                <table class="min-w-full divide-y divide-slate-200/80">
                    <thead class="bg-slate-50/80">
                        <tr class="text-left text-xs uppercase tracking-[0.25em] text-slate-500">
                            <th class="px-4 py-4 font-medium">Product</th>
                            <th class="px-4 py-4 font-medium">Category</th>
                            <th class="px-4 py-4 font-medium">Stock</th>
                            <th class="px-4 py-4 font-medium">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200/70 bg-white">
                        <tr
                            v-for="product in topProducts"
                            :key="product.id"
                            class="text-sm text-slate-700"
                        >
                            <td class="px-4 py-4 font-medium text-slate-950">{{ product.name }}</td>
                            <td class="px-4 py-4">{{ product.category }}</td>
                            <td class="px-4 py-4">{{ product.stock }}</td>
                            <td class="px-4 py-4">
                                <span
                                    class="inline-flex rounded-full px-3 py-1 text-xs font-medium"
                                    :class="{
                                        'bg-emerald-50 text-emerald-700': product.status === 'Healthy',
                                        'bg-amber-50 text-amber-700': product.status === 'Low stock',
                                        'bg-rose-50 text-rose-700': product.status === 'Urgent',
                                    }"
                                >
                                    {{ product.status }}
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>
    </main>

    <aside class="space-y-3 xl:col-start-3 xl:h-full">
        <section class="admin-card px-5 py-5 sm:px-6">
            <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-500">Team pulse</p>
            <h3 class="mt-2 text-xl font-semibold text-slate-950">Recent activity</h3>

            <ul class="mt-6 space-y-4">
                <li
                    v-for="activity in activities"
                    :key="activity.title"
                    class="flex items-start gap-3 rounded-2xl bg-slate-50 px-4 py-4"
                >
                    <span class="mt-1 h-3 w-3 rounded-full" :class="activity.accent"></span>
                    <div>
                        <p class="text-sm font-medium text-slate-900">{{ activity.title }}</p>
                        <p class="mt-1 text-sm text-slate-500">{{ activity.meta }}</p>
                    </div>
                </li>
            </ul>
        </section>

        <section class="admin-card overflow-hidden">
            <div class="bg-[radial-gradient(circle_at_top_left,_rgba(14,165,233,0.22),_transparent_38%),linear-gradient(135deg,_#0f172a,_#1e293b)] px-5 py-6 text-white sm:px-6">
                <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-300">Next milestone</p>
                <h3 class="mt-3 font-display text-2xl font-bold">Build your first CRUD module.</h3>
                <p class="mt-3 max-w-sm text-sm leading-6 text-slate-300">
                    Products is the perfect next feature: list view, create form, edit flow, validation, and seeded demo data.
                </p>
            </div>
        </section>
    </aside>
</template>
