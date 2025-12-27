<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

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
                    <table class="min-w-full text-left text-sm">
                        <thead class="border-b text-xs uppercase text-gray-500">
                            <tr>
                                <th class="py-2 pr-4">Name</th>
                                <th class="py-2 pr-4">Email</th>
                                <th class="py-2 pr-4">Role</th>
                                <th class="py-2 pr-4">Status</th>
                                <th class="py-2 pr-4"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="user in props.users"
                                :key="user.id"
                                class="border-b last:border-b-0"
                            >
                                <td class="py-2 pr-4 text-gray-900">
                                    {{ user.name }}
                                </td>
                                <td class="py-2 pr-4 text-gray-600">
                                    {{ user.email }}
                                </td>
                                <td class="py-2 pr-4">
                                    <span v-if="props.roles.length === 1" class="text-gray-700">
                                        {{ user.role }}
                                    </span>
                                    <select
                                        v-else
                                        :value="user.role"
                                        class="rounded-md border border-gray-300 px-2 py-1 text-sm focus:border-gray-900 focus:outline-none focus:ring-1 focus:ring-gray-900"
                                        :disabled="
                                            user.id === currentUserId ||
                                            (user.role === 'Owner' && !isOwner)
                                        "
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
                                </td>
                                <td class="py-2 pr-4 text-gray-700">
                                    {{ user.is_active ? 'Active' : 'Inactive' }}
                                </td>
                                <td class="py-2 pr-4 text-right">
                                    <div class="flex items-center justify-end gap-3">
                                        <button
                                            type="button"
                                            class="text-sm font-medium text-gray-600 hover:text-gray-900"
                                            :disabled="!user.is_active"
                                            @click="resetPassword(user.id)"
                                        >
                                            Reset password
                                        </button>
                                        <button
                                            v-if="user.is_active"
                                            type="button"
                                            class="text-sm font-medium text-red-600 hover:text-red-700"
                                            :disabled="
                                                user.id === currentUserId ||
                                                (user.role === 'Owner' && !isOwner)
                                            "
                                            @click="updateStatus(user.id, false)"
                                        >
                                            Deactivate
                                        </button>
                                        <button
                                            v-else
                                            type="button"
                                            class="text-sm font-medium text-gray-600 hover:text-gray-900"
                                            :disabled="
                                                user.id === currentUserId ||
                                                (user.role === 'Owner' && !isOwner)
                                            "
                                            @click="updateStatus(user.id, true)"
                                        >
                                            Activate
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="!props.users.length">
                                <td class="py-4 text-sm text-gray-500" colspan="5">
                                    No users yet.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
