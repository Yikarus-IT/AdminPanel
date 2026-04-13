<script setup>
import { computed, onMounted, reactive, ref } from 'vue';
import { asCurrency } from './formatters';

const products = ref([]);
const categories = ref([]);
const productView = ref('active');
const isLoadingProducts = ref(false);
const isLoadingCategories = ref(false);
const isSaving = ref(false);
const feedback = reactive({ type: '', message: '' });
const errors = reactive({});
const editingProductId = ref(null);
const form = reactive({
    name: '',
    categoryId: '',
    sku: '',
    price: '',
    stock: '',
    status: 'Healthy',
    description: '',
});

const statusOptions = ['Healthy', 'Low stock', 'Urgent'];
const isEditing = computed(() => editingProductId.value !== null);
const isViewingDeletedProducts = computed(() => productView.value === 'deleted');

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
    form.categoryId = categories.value[0] ? String(categories.value[0].id) : '';
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
    form.categoryId = String(product.category_id);
    form.sku = product.sku;
    form.price = product.price;
    form.stock = product.stock;
    form.status = product.status;
    form.description = product.description ?? '';
    resetErrors();
    clearFeedback();
}

async function fetchProducts() {
    isLoadingProducts.value = true;

    try {
        const response = await window.axios.get(`/products${isViewingDeletedProducts.value ? '?view=deleted' : ''}`);
        products.value = response.data.data;
    } catch {
        setFeedback('error', 'Unable to load products right now.');
    } finally {
        isLoadingProducts.value = false;
    }
}

async function fetchCategories() {
    isLoadingCategories.value = true;

    try {
        const response = await window.axios.get('/product-categories');
        categories.value = response.data.data;

        if (!categories.value.some((category) => String(category.id) === form.categoryId)) {
            form.categoryId = categories.value[0] ? String(categories.value[0].id) : '';
        }
    } catch {
        setFeedback('error', 'Unable to load product categories right now.');
    } finally {
        isLoadingCategories.value = false;
    }
}

async function saveProduct() {
    isSaving.value = true;
    resetErrors();
    clearFeedback();

    const payload = {
        name: form.name,
        category_id: Number(form.categoryId),
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

async function toggleProductView() {
    productView.value = isViewingDeletedProducts.value ? 'active' : 'deleted';
    clearFeedback();
    resetForm();
    await fetchProducts();
}

onMounted(async () => {
    await Promise.all([fetchProducts(), fetchCategories()]);
});
</script>

<template>
    <main class="xl:col-start-2 xl:min-h-0">
        <section class="admin-card flex h-full min-h-[calc(100vh-1.5rem)] flex-col px-5 py-5 sm:px-6 xl:min-h-0">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.35em] text-slate-500">Products module</p>
                    <h2 class="mt-2 font-display text-3xl font-bold tracking-tight text-slate-950">
                        {{ isViewingDeletedProducts ? 'Review archived products' : 'Manage your catalog' }}
                    </h2>
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
                        <div class="flex items-center gap-2">
                            <button
                                class="rounded-full border border-slate-300 bg-white px-3 py-1 text-xs font-medium text-slate-700 transition hover:border-slate-400 hover:text-slate-950"
                                type="button"
                                @click="toggleProductView"
                            >
                                {{ isViewingDeletedProducts ? 'View active' : 'View deleted' }}
                            </button>
                            <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-600">
                                {{ products.length }} {{ isViewingDeletedProducts ? 'deleted' : 'items' }}
                            </span>
                        </div>
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
                                        <div v-if="!isViewingDeletedProducts" class="flex gap-2">
                                            <button class="rounded-full bg-slate-100 px-3 py-2 text-xs font-medium text-slate-700 transition hover:bg-slate-200" type="button" @click="fillForm(product)">
                                                Edit
                                            </button>
                                            <button class="rounded-full bg-rose-50 px-3 py-2 text-xs font-medium text-rose-700 transition hover:bg-rose-100" type="button" @click="deleteProduct(product)">
                                                Delete
                                            </button>
                                        </div>
                                        <span v-else class="inline-flex rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-500">
                                            Archived
                                        </span>
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

            <form v-if="!isViewingDeletedProducts" class="mt-6 space-y-4" @submit.prevent="saveProduct">
                <div>
                    <label class="text-sm font-medium text-slate-700" for="name">Name</label>
                    <input id="name" v-model="form.name" class="admin-input mt-2" type="text">
                    <p v-if="errors.name" class="mt-2 text-sm text-rose-600">{{ errors.name[0] }}</p>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="text-sm font-medium text-slate-700" for="category">Category</label>
                        <select id="category" v-model="form.categoryId" class="admin-input mt-2" :disabled="isLoadingCategories || !categories.length">
                            <option v-if="!categories.length" value="">
                                {{ isLoadingCategories ? 'Loading categories...' : 'No categories available' }}
                            </option>
                            <option v-for="category in categories" :key="category.id" :value="String(category.id)">{{ category.name }}</option>
                        </select>
                        <p v-if="errors.category_id" class="mt-2 text-sm text-rose-600">{{ errors.category_id[0] }}</p>
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
                        :disabled="isSaving || isLoadingCategories || !categories.length"
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
            <div v-else class="mt-6 rounded-3xl bg-slate-50 px-5 py-5 text-sm leading-7 text-slate-600">
                Deleted products stay in the database for future statistics, references, and audit-friendly history.
                Switch back to <span class="font-medium text-slate-900">View active</span> to create or edit catalog items.
            </div>
        </section>
    </aside>
</template>
