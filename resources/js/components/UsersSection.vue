<script setup>
import { computed, onMounted, reactive, ref } from 'vue';

const users = ref([]);
const isLoadingUsers = ref(false);
const isSavingUser = ref(false);
const userFeedback = reactive({ type: '', message: '' });
const userErrors = reactive({});
const editingUserId = ref(null);
const userForm = reactive({
    name: '',
    email: '',
    password: '',
});

const isEditingUser = computed(() => editingUserId.value !== null);

function setUserFeedback(type, message) {
    userFeedback.type = type;
    userFeedback.message = message;
}

function clearUserFeedback() {
    userFeedback.type = '';
    userFeedback.message = '';
}

function resetUserErrors() {
    Object.keys(userErrors).forEach((key) => delete userErrors[key]);
}

function resetUserForm() {
    editingUserId.value = null;
    userForm.name = '';
    userForm.email = '';
    userForm.password = '';
    resetUserErrors();
}

function fillUserForm(user) {
    editingUserId.value = user.id;
    userForm.name = user.name;
    userForm.email = user.email;
    userForm.password = '';
    resetUserErrors();
    clearUserFeedback();
}

async function fetchUsers() {
    isLoadingUsers.value = true;

    try {
        const response = await window.axios.get('/users');
        users.value = response.data.data;
    } catch {
        setUserFeedback('error', 'Unable to load users right now.');
    } finally {
        isLoadingUsers.value = false;
    }
}

async function saveUser() {
    isSavingUser.value = true;
    resetUserErrors();
    clearUserFeedback();

    const payload = {
        name: userForm.name,
        email: userForm.email,
        password: userForm.password,
    };

    try {
        if (isEditingUser.value) {
            if (!payload.password) {
                delete payload.password;
            }

            const response = await window.axios.put(`/users/${editingUserId.value}`, payload);
            const index = users.value.findIndex((user) => user.id === editingUserId.value);

            if (index !== -1) {
                users.value[index] = response.data.data;
            }

            setUserFeedback('success', response.data.message);
        } else {
            const response = await window.axios.post('/users', payload);
            users.value.unshift(response.data.data);
            setUserFeedback('success', response.data.message);
        }

        resetUserForm();
    } catch (error) {
        if (error.response?.status === 422) {
            Object.assign(userErrors, error.response.data.errors ?? {});
            setUserFeedback('error', 'Please fix the highlighted user fields and try again.');
        } else {
            setUserFeedback('error', 'Something went wrong while saving the user.');
        }
    } finally {
        isSavingUser.value = false;
    }
}

async function deleteUser(user) {
    const shouldDelete = window.confirm(`Delete "${user.name}"?`);

    if (!shouldDelete) {
        return;
    }

    clearUserFeedback();

    try {
        const response = await window.axios.delete(`/users/${user.id}`);
        users.value = users.value.filter((item) => item.id !== user.id);

        if (editingUserId.value === user.id) {
            resetUserForm();
        }

        setUserFeedback('success', response.data.message);
    } catch {
        setUserFeedback('error', 'Unable to delete that user right now.');
    }
}

onMounted(fetchUsers);
</script>

<template>
    <main class="xl:col-start-2 xl:min-h-0">
        <section class="admin-card flex h-full min-h-[calc(100vh-1.5rem)] flex-col px-5 py-5 sm:px-6 xl:min-h-0">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.35em] text-slate-500">Users module</p>
                    <h2 class="mt-2 font-display text-3xl font-bold tracking-tight text-slate-950">Manage your team</h2>
                    <p class="mt-3 max-w-2xl text-sm leading-7 text-slate-600">
                        This section uses Laravel's users table so you can test another real database-backed module right away.
                    </p>
                </div>

                <button
                    class="rounded-full border border-slate-300 bg-white px-5 py-3 text-sm font-medium text-slate-700 transition hover:border-slate-400 hover:text-slate-950"
                    type="button"
                    @click="resetUserForm"
                >
                    {{ isEditingUser ? 'Create new user' : 'Reset form' }}
                </button>
            </div>

            <div
                v-if="userFeedback.message"
                class="mt-6 rounded-2xl px-4 py-3 text-sm font-medium"
                :class="userFeedback.type === 'success' ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700'"
            >
                {{ userFeedback.message }}
            </div>

            <div class="mt-6 flex min-h-0 flex-1 overflow-hidden rounded-3xl border border-slate-200/80 bg-white">
                <div class="flex min-h-0 w-full flex-col">
                    <div class="flex items-center justify-between border-b border-slate-200 px-4 py-4">
                        <p class="text-sm font-semibold text-slate-900">User list</p>
                        <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-600">{{ users.length }} users</span>
                    </div>

                    <div v-if="isLoadingUsers" class="px-4 py-8 text-sm text-slate-500">Loading users...</div>

                    <div v-else class="min-h-0 flex-1 overflow-auto">
                        <table class="min-w-full divide-y divide-slate-200/80">
                            <thead class="sticky top-0 z-10 bg-slate-50/95 backdrop-blur">
                                <tr class="text-left text-xs uppercase tracking-[0.25em] text-slate-500">
                                    <th class="px-4 py-4 font-medium">Name</th>
                                    <th class="px-4 py-4 font-medium">Email</th>
                                    <th class="px-4 py-4 font-medium">Joined</th>
                                    <th class="px-4 py-4 font-medium">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200/70">
                                <tr v-for="user in users" :key="user.id" class="text-sm text-slate-700">
                                    <td class="px-4 py-4 align-top font-medium text-slate-950">{{ user.name }}</td>
                                    <td class="px-4 py-4 align-top">{{ user.email }}</td>
                                    <td class="px-4 py-4 align-top">{{ new Date(user.created_at).toLocaleDateString('en-US') }}</td>
                                    <td class="px-4 py-4 align-top">
                                        <div class="flex gap-2">
                                            <button class="rounded-full bg-slate-100 px-3 py-2 text-xs font-medium text-slate-700 transition hover:bg-slate-200" type="button" @click="fillUserForm(user)">
                                                Edit
                                            </button>
                                            <button class="rounded-full bg-rose-50 px-3 py-2 text-xs font-medium text-rose-700 transition hover:bg-rose-100" type="button" @click="deleteUser(user)">
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
            <p class="text-xs font-semibold uppercase tracking-[0.35em] text-slate-500">{{ isEditingUser ? 'Edit user' : 'Create user' }}</p>
            <h3 class="mt-2 text-2xl font-semibold text-slate-950">{{ isEditingUser ? 'Update team member' : 'Add a new team member' }}</h3>
            <p class="mt-3 text-sm leading-7 text-slate-600">
                This module shows another CRUD flow using Laravel's default users table, with secure password handling.
            </p>

            <form class="mt-6 space-y-4" @submit.prevent="saveUser">
                <div>
                    <label class="text-sm font-medium text-slate-700" for="user-name">Name</label>
                    <input id="user-name" v-model="userForm.name" class="admin-input mt-2" type="text">
                    <p v-if="userErrors.name" class="mt-2 text-sm text-rose-600">{{ userErrors.name[0] }}</p>
                </div>

                <div>
                    <label class="text-sm font-medium text-slate-700" for="user-email">Email</label>
                    <input id="user-email" v-model="userForm.email" class="admin-input mt-2" type="email">
                    <p v-if="userErrors.email" class="mt-2 text-sm text-rose-600">{{ userErrors.email[0] }}</p>
                </div>

                <div>
                    <label class="text-sm font-medium text-slate-700" for="user-password">
                        {{ isEditingUser ? 'New password (optional)' : 'Password' }}
                    </label>
                    <input id="user-password" v-model="userForm.password" class="admin-input mt-2" type="password">
                    <p v-if="userErrors.password" class="mt-2 text-sm text-rose-600">{{ userErrors.password[0] }}</p>
                </div>

                <div class="flex gap-3">
                    <button
                        class="flex-1 rounded-full bg-slate-950 px-5 py-3 text-sm font-medium text-white transition hover:bg-slate-800 disabled:cursor-not-allowed disabled:bg-slate-400"
                        type="submit"
                        :disabled="isSavingUser"
                    >
                        {{ isSavingUser ? 'Saving...' : (isEditingUser ? 'Update user' : 'Create user') }}
                    </button>
                    <button
                        class="rounded-full border border-slate-300 bg-white px-5 py-3 text-sm font-medium text-slate-700 transition hover:border-slate-400 hover:text-slate-950"
                        type="button"
                        @click="resetUserForm"
                    >
                        Clear
                    </button>
                </div>
            </form>
        </section>
    </aside>
</template>
