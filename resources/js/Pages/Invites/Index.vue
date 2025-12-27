<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps<{
    invites: Array<{
        id: number;
        email: string;
        role_name: string;
        expires_at: string;
        used_at: string | null;
        resent_at: string | null;
        created_at: string;
    }>;
    roles: string[];
}>();

const form = useForm({
    email: '',
    role_name: props.roles[0] ?? 'Sales',
});

const formatDateTime = (value: string | null) => {
    if (! value) {
        return '-';
    }

    const date = new Date(value);
    if (Number.isNaN(date.getTime())) {
        return value;
    }

    return new Intl.DateTimeFormat('en-US', {
        year: 'numeric',
        month: 'short',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit',
    }).format(date);
};

const statusLabel = (invite: (typeof props.invites)[number]) => {
    if (invite.used_at) {
        return 'Used';
    }

    const expiresAt = new Date(invite.expires_at);
    if (!Number.isNaN(expiresAt.getTime()) && expiresAt < new Date()) {
        return 'Expired';
    }

    if (invite.resent_at) {
        return 'Resent';
    }

    return 'Pending';
};
</script>

<template>
    <Head title="Invites" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Invites
                </h2>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl space-y-6 px-6">
                <form
                    class="rounded-lg border border-gray-200 bg-white p-6"
                    @submit.prevent="form.post(route('invites.store'))"
                >
                    <div class="grid gap-5 md:grid-cols-[1.5fr_1fr_auto] md:items-end">
                        <div>
                            <InputLabel for="email" value="Email" class="text-gray-800" />
                            <TextInput
                                id="email"
                                v-model="form.email"
                                type="email"
                                class="mt-1 block w-full"
                                autocomplete="off"
                            />
                            <InputError class="mt-2" :message="form.errors.email" />
                        </div>
                        <div>
                            <InputLabel for="role_name" value="Role" class="text-gray-800" />
                            <select
                                id="role_name"
                                v-model="form.role_name"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            >
                                <option v-for="role in props.roles" :key="role" :value="role">
                                    {{ role }}
                                </option>
                            </select>
                            <InputError class="mt-2" :message="form.errors.role_name" />
                        </div>
                        <div class="flex items-end">
                            <PrimaryButton :disabled="form.processing">
                                Send invite
                            </PrimaryButton>
                        </div>
                    </div>
                </form>

                <div class="rounded-lg border border-gray-200 bg-white p-6">
                    <div class="text-lg font-semibold text-gray-900">
                        Recent invites
                    </div>
                    <div class="mt-4 overflow-x-auto">
                        <table class="min-w-full text-left text-sm">
                            <thead class="border-b text-xs uppercase text-gray-500">
                                <tr>
                                    <th class="py-2 pr-4">Email</th>
                                    <th class="py-2 pr-4">Role</th>
                                    <th class="py-2 pr-4">Status</th>
                                    <th class="py-2 pr-4">Expires</th>
                                    <th class="py-2 pr-4">Used</th>
                                    <th class="py-2 pr-4"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="invite in props.invites"
                                    :key="invite.id"
                                    class="border-b last:border-b-0"
                                >
                                    <td class="py-2 pr-4 text-gray-900">
                                        {{ invite.email }}
                                    </td>
                                    <td class="py-2 pr-4 text-gray-600">
                                        {{ invite.role_name }}
                                    </td>
                                    <td class="py-2 pr-4">
                                        <span
                                            class="rounded-full bg-gray-100 px-2 py-1 text-xs font-medium text-gray-600"
                                        >
                                            {{ statusLabel(invite) }}
                                        </span>
                                    </td>
                                    <td class="py-2 pr-4 text-gray-600">
                                        {{ formatDateTime(invite.expires_at) }}
                                    </td>
                                    <td class="py-2 pr-4 text-gray-600">
                                        {{ formatDateTime(invite.used_at) }}
                                    </td>
                                    <td class="py-2 pr-4 text-right">
                                        <Link
                                            v-if="!invite.used_at"
                                            as="button"
                                            method="post"
                                            class="text-sm font-medium text-gray-600 hover:text-gray-900"
                                            :href="route('invites.resend', invite.id)"
                                        >
                                            Resend
                                        </Link>
                                    </td>
                                </tr>
                                <tr v-if="!props.invites.length">
                                    <td class="py-4 text-sm text-gray-500" colspan="6">
                                        No invites yet.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
