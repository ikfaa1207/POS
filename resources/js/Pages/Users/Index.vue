<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Dropdown from '@/Components/Dropdown.vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { useCan } from '@/composables/useCan';

type UserRow = {
    id: number;
    name: string;
    email: string;
    role: string;
    is_active: boolean;
};

const props = defineProps<{
    users: UserRow[];
    roles: string[];
}>();

const page = usePage();
const currentUserId = computed(() => (page.props.auth.user as { id: number } | null)?.id);
const isOwner = computed(() => (page.props.auth.roles as string[] | undefined)?.includes('Owner'));
const { can } = useCan();
const canManageUsers = computed(() => can('user.manage'));
const queryParams = new URLSearchParams(window.location.search);
const search = ref(queryParams.get('search') ?? '');
const focusIdValue = Number(queryParams.get('focus') ?? 0);
const focusUserId = Number.isNaN(focusIdValue) || focusIdValue === 0 ? null : focusIdValue;

const filteredUsers = computed(() => {
    const term = search.value.trim().toLowerCase();
    if (!term) {
        return props.users;
    }

    return props.users.filter((user) => {
        return (
            user.name.toLowerCase().includes(term) ||
            user.email.toLowerCase().includes(term) ||
            user.role.toLowerCase().includes(term)
        );
    });
});

const initials = (name: string) => {
    return name
        .split(' ')
        .filter(Boolean)
        .slice(0, 2)
        .map((part) => part[0]?.toUpperCase() ?? '')
        .join('');
};

const canManageRow = (user: UserRow) => {
    return (
        canManageUsers.value &&
        !(user.id === currentUserId.value || (user.role === 'Owner' && !isOwner.value))
    );
};

const formatIdentifier = (id: number) => {
    return `U${String(id).padStart(4, '0')}`;
};

const canEditRole = (user: UserRow) => {
    return props.roles.length > 1 && canManageRow(user);
};

const statusClass = (isActive: boolean) => {
    return isActive
        ? 'bg-green-100 text-green-700'
        : 'bg-gray-200 text-gray-700';
};

const updateRole = (userId: number, role: string) => {
    router.patch(
        route('users.role', userId),
        { role },
        { preserveScroll: true },
    );
};

const updateStatus = (userId: number, isActive: boolean) => {
    router.patch(
        route('users.status', userId),
        { is_active: isActive },
        { preserveScroll: true },
    );
};

const resetPassword = (userId: number) => {
    router.post(route('users.reset', userId), {}, { preserveScroll: true });
};
</script>

<template>
    <Head title="Users" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Users
                </h2>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl px-6">
                <div class="rounded-lg border border-gray-200 bg-white p-6">
                    <div class="mb-4 flex justify-end">
                        <input
                            v-model="search"
                            type="text"
                            class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-gray-900 focus:outline-none focus:ring-1 focus:ring-gray-900 sm:w-64"
                            placeholder="Search users..."
                        />
                    </div>
                    <table class="min-w-full text-left text-sm">
                        <thead class="border-b text-xs uppercase text-gray-500">
                            <tr>
                                <th class="py-2 pr-4">Name</th>
                                <th class="py-2 pr-4">Email</th>
                                <th class="py-2 pr-4">Role</th>
                                <th class="py-2 pr-4">Status</th>
                                <th class="py-2 pr-0 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="user in filteredUsers"
                                :key="user.id"
                                :id="`user-${user.id}`"
                                :class="[
                                    'border-b last:border-b-0',
                                    user.id === focusUserId ? 'bg-amber-50' : '',
                                ]"
                            >
                                <td class="py-3 pr-4 align-middle">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="flex h-8 w-8 items-center justify-center rounded-full bg-gray-100 text-xs font-semibold text-gray-700"
                                        >
                                            {{ initials(user.name) }}
                                        </div>
                                        <div>
                                            <div class="font-semibold text-gray-900">
                                                {{ user.name }}
                                            </div>
                                            <div class="text-xs text-gray-500">
                                                ID: {{ formatIdentifier(user.id) }} · {{ user.role }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3 pr-4 align-middle text-gray-600">
                                    {{ user.email }}
                                </td>
                                <td class="py-3 pr-4 align-middle">
                                    <span
                                        v-if="!canEditRole(user)"
                                        class="inline-flex items-center rounded-md border border-gray-200 bg-gray-50 px-2.5 py-1 text-sm text-gray-700"
                                    >
                                        {{ user.role }}
                                    </span>
                                    <div v-else class="relative inline-flex items-center">
                                        <select
                                            :value="user.role"
                                            class="appearance-none rounded-md border border-gray-300 bg-none bg-white px-2 py-1 pr-8 text-sm shadow-sm focus:border-gray-900 focus:outline-none focus:ring-1 focus:ring-gray-900"
                                            @change="
                                                updateRole(
                                                    user.id,
                                                    ($event.target as HTMLSelectElement).value,
                                                )
                                            "
                                        >
                                            <option
                                                v-for="role in props.roles"
                                                :key="role"
                                                :value="role"
                                                :disabled="role === 'Owner' && !isOwner"
                                            >
                                                {{ role }}
                                            </option>
                                        </select>
                                        <svg
                                            class="pointer-events-none absolute right-2 h-4 w-4 text-gray-500"
                                            viewBox="0 0 20 20"
                                            fill="currentColor"
                                            aria-hidden="true"
                                        >
                                            <path
                                                fill-rule="evenodd"
                                                d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.06 1.06l-4.24 4.25a.75.75 0 01-1.06 0L5.21 8.29a.75.75 0 01.02-1.08z"
                                                clip-rule="evenodd"
                                            />
                                        </svg>
                                    </div>
                                </td>
                                <td class="py-3 pr-4 align-middle">
                                    <span
                                        class="rounded-full px-2 py-1 text-xs font-semibold"
                                        :class="statusClass(user.is_active)"
                                    >
                                        {{ user.is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="py-3 pr-0 align-middle text-right">
                                    <Dropdown align="right" width="48">
                                        <template #trigger="{ open }">
                                            <button
                                                type="button"
                                                :class="[
                                                    'rounded-md px-2 py-1 text-sm transition',
                                                    open
                                                        ? 'bg-gray-100 text-gray-900'
                                                        : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900',
                                                ]"
                                                aria-label="User actions"
                                            >
                                                <svg
                                                    class="h-4 w-4"
                                                    viewBox="0 0 20 20"
                                                    fill="currentColor"
                                                    aria-hidden="true"
                                                >
                                                    <circle cx="10" cy="4" r="1.5" />
                                                    <circle cx="10" cy="10" r="1.5" />
                                                    <circle cx="10" cy="16" r="1.5" />
                                                </svg>
                                            </button>
                                        </template>
                                        <template #content>
                                            <button
                                                type="button"
                                                class="block w-full px-4 py-2 text-start text-sm text-gray-700 hover:bg-gray-100 disabled:text-gray-400"
                                                :disabled="!user.is_active"
                                                @click="resetPassword(user.id)"
                                            >
                                                Reset password
                                            </button>
                                            <button
                                                v-if="user.is_active"
                                                type="button"
                                                class="block w-full px-4 py-2 text-start text-sm text-red-600 hover:bg-gray-100 disabled:text-gray-300"
                                                :disabled="!canManageRow(user)"
                                                @click="updateStatus(user.id, false)"
                                            >
                                                Deactivate
                                            </button>
                                            <button
                                                v-else
                                                type="button"
                                                class="block w-full px-4 py-2 text-start text-sm text-gray-700 hover:bg-gray-100 disabled:text-gray-300"
                                                :disabled="!canManageRow(user)"
                                                @click="updateStatus(user.id, true)"
                                            >
                                                Activate
                                            </button>
                                        </template>
                                    </Dropdown>
                                </td>
                            </tr>
                            <tr v-if="!filteredUsers.length">
                                <td class="py-4 text-sm text-gray-500" colspan="5">
                                    <div class="flex items-center justify-between gap-3">
                                        <span>No users found.</span>
                                        <button
                                            v-if="search"
                                            type="button"
                                            class="text-sm font-medium text-gray-600 hover:text-gray-900"
                                            @click="search = ''"
                                        >
                                            Clear search
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
