<script setup>
import { computed, onMounted, reactive, ref } from 'vue';
import DashboardStatCard from './components/DashboardStatCard.vue';
import SidebarNavItem from './components/SidebarNavItem.vue';

const navigationItems = [
    { label: 'Dashboard', icon: 'grid' },
    { label: 'Products', icon: 'box' },
    { label: 'Orders', icon: 'cart' },
    { label: 'Customers', icon: 'users' },
    { label: 'Analytics', icon: 'chart' },
    { label: 'Settings', icon: 'settings' },
];

const activities = [
    { title: 'Andrea posted a new shipment update', meta: '3 minutes ago', accent: 'bg-emerald-400' },
    { title: 'Quarterly report exported to finance', meta: '18 minutes ago', accent: 'bg-sky-400' },
    { title: 'Inventory alert triggered for Standing Desk', meta: '42 minutes ago', accent: 'bg-amber-400' },
    { title: 'Customer refund approved by support', meta: '1 hour ago', accent: 'bg-rose-400' },
];

const activeSection = ref('Dashboard');
const products = ref([]);
const isLoadingProducts = ref(false);
const isSaving = ref(false);
const feedback = reactive({ type: '', message: '' });
const errors = reactive({});
const editingProductId = ref(null);
const form = reactive({
    name: '',
    category: '',
    sku: '',
    price: '',
    stock: '',
    status: 'Healthy',
    description: '',
});

const statusOptions = ['Healthy', 'Low stock', 'Urgent'];

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
const isEditing = computed(() => editingProductId.value !== null);

function asCurrency(value) {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }).format(Number(value || 0));
}

function setFeedback(type, message) {
    feedback.type = type;
    feedback.message = message;
}

function clearFeedback() {
    feedback.type = '';
    feedback.message = '';
}

function resetErrors() {
    Object.keys(errors).forEach((key) => delete errors[key]);
}

function resetForm() {
    editingProductId.value = null;
    form.name = '';
    form.category = '';
    form.sku = '';
    form.price = '';
    form.stock = '';
    form.status = 'Healthy';
    form.description = '';
    resetErrors();
}

function fillForm(product) {
    editingProductId.value = product.id;
    form.name = product.name;
    form.category = product.category;
    form.sku = product.sku;
    form.price = product.price;
    form.stock = product.stock;
    form.status = product.status;
    form.description = product.description ?? '';
    resetErrors();
    clearFeedback();
    activeSection.value = 'Products';
}

async function fetchProducts() {
    isLoadingProducts.value = true;

    try {
        const response = await window.axios.get('/products');
        products.value = response.data.data;
    } catch {
        setFeedback('error', 'Unable to load products right now.');
    } finally {
        isLoadingProducts.value = false;
    }
}

async function saveProduct() {
    isSaving.value = true;
    resetErrors();
    clearFeedback();

    const payload = {
        name: form.name,
        category: form.category,
        sku: form.sku,
        price: form.price,
        stock: form.stock,
        status: form.status,
        description: form.description,
    };

    try {
        if (isEditing.value) {
            const response = await window.axios.put(`/products/${editingProductId.value}`, payload);
            const index = products.value.findIndex((product) => product.id === editingProductId.value);

            if (index !== -1) {
                products.value[index] = response.data.data;
            }

            setFeedback('success', response.data.message);
        } else {
            const response = await window.axios.post('/products', payload);
            products.value.unshift(response.data.data);
            setFeedback('success', response.data.message);
        }

        resetForm();
    } catch (error) {
        if (error.response?.status === 422) {
            Object.assign(errors, error.response.data.errors ?? {});
            setFeedback('error', 'Please fix the highlighted fields and try again.');
        } else {
            setFeedback('error', 'Something went wrong while saving the product.');
        }
    } finally {
        isSaving.value = false;
    }
}

async function deleteProduct(product) {
    const shouldDelete = window.confirm(`Delete "${product.name}"?`);

    if (!shouldDelete) {
        return;
    }

    clearFeedback();

    try {
        const response = await window.axios.delete(`/products/${product.id}`);
        products.value = products.value.filter((item) => item.id !== product.id);

        if (editingProductId.value === product.id) {
            resetForm();
        }

        setFeedback('success', response.data.message);
    } catch {
        setFeedback('error', 'Unable to delete that product right now.');
    }
}

function openSection(label) {
    activeSection.value = label;

    if (label === 'Products') {
        clearFeedback();
    }
}

onMounted(fetchProducts);
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

            <template v-if="activeSection === 'Dashboard'">
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
                                    @click="openSection('Products')"
                                >
                                    New product
                                </button>
                                <button class="rounded-full border border-slate-300 bg-white px-5 py-3 text-sm font-medium text-slate-700 transition hover:border-slate-400 hover:text-slate-950">
                                    Export report
                                </button>
                            </div>
                        </div>

                        <div class="mt-8 grid gap-4 md:grid-cols-2 2xl:grid-cols-4">
                            <DashboardStatCard v-for="stat in stats" :key="stat.title" :stat="stat" />
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
                                    <tr v-for="product in topProducts" :key="product.id" class="text-sm text-slate-700">
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

            <template v-else-if="activeSection === 'Products'">
                <main class="xl:col-start-2 xl:min-h-0">
                    <section class="admin-card flex h-full min-h-[calc(100vh-1.5rem)] flex-col px-5 py-5 sm:px-6 xl:min-h-0">
                        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-[0.35em] text-slate-500">Products module</p>
                                <h2 class="mt-2 font-display text-3xl font-bold tracking-tight text-slate-950">Manage your catalog</h2>
                                <p class="mt-3 max-w-2xl text-sm leading-7 text-slate-600">
                                    This screen demonstrates real CRUD work with Laravel validation, Vue forms,
                                    API calls, state updates, and a clean admin interface.
                                </p>
                            </div>

                            <button
                                class="cursor-pointer rounded-full border border-slate-300 bg-white px-5 py-3 text-sm font-medium text-slate-700 transition hover:border-slate-400 hover:text-slate-950"
                                type="button"
                                @click="resetForm"
                            >
                                {{ isEditing ? 'Create new product' : 'Reset form' }}
                            </button>
                        </div>

                        <div
                            v-if="feedback.message"
                            class="mt-6 rounded-2xl px-4 py-3 text-sm font-medium"
                            :class="feedback.type === 'success' ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700'"
                        >
                            {{ feedback.message }}
                        </div>

                        <div class="mt-6 flex min-h-0 flex-1 overflow-hidden rounded-3xl border border-slate-200/80 bg-white">
                            <div class="flex min-h-0 w-full flex-col">
                                <div class="flex items-center justify-between border-b border-slate-200 px-4 py-4">
                                    <p class="text-sm font-semibold text-slate-900">Product list</p>
                                    <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-600">{{ products.length }} items</span>
                                </div>

                                <div v-if="isLoadingProducts" class="px-4 py-8 text-sm text-slate-500">Loading products...</div>

                                <div v-else class="min-h-0 flex-1 overflow-auto">
                                    <table class="min-w-full divide-y divide-slate-200/80">
                                        <thead class="sticky top-0 z-10 bg-slate-50/95 backdrop-blur">
                                            <tr class="text-left text-xs uppercase tracking-[0.25em] text-slate-500">
                                                <th class="px-4 py-4 font-medium">Product</th>
                                                <th class="px-4 py-4 font-medium">SKU</th>
                                                <th class="px-4 py-4 font-medium">Price</th>
                                                <th class="px-4 py-4 font-medium">Stock</th>
                                                <th class="px-4 py-4 font-medium">Status</th>
                                                <th class="px-4 py-4 font-medium">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-slate-200/70">
                                            <tr v-for="product in products" :key="product.id" class="text-sm text-slate-700">
                                                <td class="px-4 py-4 align-top">
                                                    <p class="font-medium text-slate-950">{{ product.name }}</p>
                                                    <p class="mt-1 text-slate-500">{{ product.category }}</p>
                                                </td>
                                                <td class="px-4 py-4 align-top">{{ product.sku }}</td>
                                                <td class="px-4 py-4 align-top">{{ asCurrency(product.price) }}</td>
                                                <td class="px-4 py-4 align-top">{{ product.stock }}</td>
                                                <td class="px-4 py-4 align-top">
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
                                                <td class="px-4 py-4 align-top">
                                                    <div class="flex gap-2">
                                                        <button class="rounded-full bg-slate-100 px-3 py-2 text-xs font-medium text-slate-700 transition hover:bg-slate-200" type="button" @click="fillForm(product)">
                                                            Edit
                                                        </button>
                                                        <button class="rounded-full bg-rose-50 px-3 py-2 text-xs font-medium text-rose-700 transition hover:bg-rose-100" type="button" @click="deleteProduct(product)">
                                                            Delete
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </section>
                </main>

                <aside class="xl:col-start-3 xl:h-full">
                    <section class="admin-card h-full overflow-auto px-5 py-5 sm:px-6">
                        <p class="text-xs font-semibold uppercase tracking-[0.35em] text-slate-500">{{ isEditing ? 'Edit product' : 'Create product' }}</p>
                        <h3 class="mt-2 text-2xl font-semibold text-slate-950">{{ isEditing ? 'Update catalog item' : 'Add a new product' }}</h3>
                        <p class="mt-3 text-sm leading-7 text-slate-600">
                            Keep the form simple and polished so the repo shows strong CRUD fundamentals.
                        </p>

                        <form class="mt-6 space-y-4" @submit.prevent="saveProduct">
                            <div>
                                <label class="text-sm font-medium text-slate-700" for="name">Name</label>
                                <input id="name" v-model="form.name" class="admin-input mt-2" type="text">
                                <p v-if="errors.name" class="mt-2 text-sm text-rose-600">{{ errors.name[0] }}</p>
                            </div>

                            <div class="grid gap-4 sm:grid-cols-2">
                                <div>
                                    <label class="text-sm font-medium text-slate-700" for="category">Category</label>
                                    <input id="category" v-model="form.category" class="admin-input mt-2" type="text">
                                    <p v-if="errors.category" class="mt-2 text-sm text-rose-600">{{ errors.category[0] }}</p>
                                </div>

                                <div>
                                    <label class="text-sm font-medium text-slate-700" for="sku">SKU</label>
                                    <input id="sku" v-model="form.sku" class="admin-input mt-2" type="text">
                                    <p v-if="errors.sku" class="mt-2 text-sm text-rose-600">{{ errors.sku[0] }}</p>
                                </div>
                            </div>

                            <div class="grid gap-4 sm:grid-cols-2">
                                <div>
                                    <label class="text-sm font-medium text-slate-700" for="price">Price</label>
                                    <input id="price" v-model="form.price" class="admin-input mt-2" type="number" min="0" step="0.01">
                                    <p v-if="errors.price" class="mt-2 text-sm text-rose-600">{{ errors.price[0] }}</p>
                                </div>

                                <div>
                                    <label class="text-sm font-medium text-slate-700" for="stock">Stock</label>
                                    <input id="stock" v-model="form.stock" class="admin-input mt-2" type="number" min="0">
                                    <p v-if="errors.stock" class="mt-2 text-sm text-rose-600">{{ errors.stock[0] }}</p>
                                </div>
                            </div>

                            <div>
                                <label class="text-sm font-medium text-slate-700" for="status">Status</label>
                                <select id="status" v-model="form.status" class="admin-input mt-2">
                                    <option v-for="status in statusOptions" :key="status" :value="status">{{ status }}</option>
                                </select>
                                <p v-if="errors.status" class="mt-2 text-sm text-rose-600">{{ errors.status[0] }}</p>
                            </div>

                            <div>
                                <label class="text-sm font-medium text-slate-700" for="description">Description</label>
                                <textarea id="description" v-model="form.description" class="admin-input mt-2 min-h-28"></textarea>
                                <p v-if="errors.description" class="mt-2 text-sm text-rose-600">{{ errors.description[0] }}</p>
                            </div>

                            <div class="flex gap-3">
                                <button
                                    class="flex-1 cursor-pointer rounded-full bg-slate-950 px-5 py-3 text-sm font-medium text-white transition hover:bg-slate-800 disabled:cursor-not-allowed disabled:bg-slate-400"
                                    type="submit"
                                    :disabled="isSaving"
                                >
                                    {{ isSaving ? 'Saving...' : (isEditing ? 'Update product' : 'Create product') }}
                                </button>
                                <button
                                    class="cursor-pointer rounded-full border border-slate-300 bg-white px-5 py-3 text-sm font-medium text-slate-700 transition hover:border-slate-400 hover:text-slate-950"
                                    type="button"
                                    @click="resetForm"
                                >
                                    Clear
                                </button>
                            </div>
                        </form>
                    </section>
                </aside>
            </template>

            <main v-else class="xl:col-start-2 xl:col-span-2">
                <section class="admin-card px-6 py-10">
                    <p class="text-xs font-semibold uppercase tracking-[0.35em] text-slate-500">{{ activeSection }}</p>
                    <h2 class="mt-3 font-display text-3xl font-bold text-slate-950">Module placeholder</h2>
                    <p class="mt-4 max-w-2xl text-sm leading-7 text-slate-600">
                        This section is ready for expansion. Once products is finished, we can use the same Laravel + Vue pattern
                        for orders, customers, analytics, or role-based admin features.
                    </p>
                </section>
            </main>
        </div>
    </div>
</template>
