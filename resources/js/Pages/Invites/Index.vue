<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Dropdown from '@/Components/Dropdown.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { useCan } from '@/composables/useCan';

const props = defineProps<{
    invites: Array<{
        id: number;
        email: string;
        role_name: string;
        token: string;
        expires_at: string;
        used_at: string | null;
        resent_at: string | null;
        revoked_at: string | null;
        created_at: string;
        user_id: number | null;
        user_is_active: boolean | null;
    }>;
    roles: string[];
}>();

const { can } = useCan();
const canInvite = computed(() => can('user.invite'));
const canManageUsers = computed(() => can('user.manage'));

const form = useForm({
    email: '',
    role_name: props.roles[0] ?? 'Sales',
});

const tableSearch = ref('');
const copyNotice = ref<string | null>(null);

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

const formatRelative = (value: string | null) => {
    if (!value) {
        return '-';
    }

    const date = new Date(value);
    if (Number.isNaN(date.getTime())) {
        return value;
    }

    const diff = date.getTime() - Date.now();
    const abs = Math.abs(diff);
    const minute = 60 * 1000;
    const hour = 60 * minute;
    const day = 24 * hour;

    let unit: Intl.RelativeTimeFormatUnit = 'minute';
    let count = Math.round(diff / minute);

    if (abs >= day) {
        unit = 'day';
        count = Math.round(diff / day);
    } else if (abs >= hour) {
        unit = 'hour';
        count = Math.round(diff / hour);
    } else {
        unit = 'minute';
        count = Math.round(diff / minute);
    }

    const formatter = new Intl.RelativeTimeFormat('en', { numeric: 'auto' });

    return formatter.format(count, unit);
};

const isExpired = (invite: (typeof props.invites)[number]) => {
    const expiresAt = new Date(invite.expires_at);
    return !Number.isNaN(expiresAt.getTime()) && expiresAt < new Date();
};

const statusLabel = (invite: (typeof props.invites)[number]) => {
    if (invite.used_at) {
        return 'Used';
    }

    if (invite.revoked_at) {
        return 'Revoked';
    }

    if (isExpired(invite)) {
        return 'Expired';
    }

    if (invite.resent_at) {
        return 'Resent';
    }

    return 'Pending';
};

const statusClass = (status: string) => {
    switch (status) {
        case 'Pending':
            return 'bg-amber-100 text-amber-700';
        case 'Used':
            return 'bg-green-100 text-green-700';
        case 'Resent':
            return 'bg-blue-100 text-blue-700';
        case 'Revoked':
            return 'bg-gray-200 text-gray-700';
        case 'Expired':
            return 'bg-gray-100 text-gray-600';
        default:
            return 'bg-gray-100 text-gray-600';
    }
};

const filteredInvites = computed(() => {
    const term = tableSearch.value.trim().toLowerCase();
    if (!term) {
        return props.invites;
    }

    return props.invites.filter((invite) => {
        const status = statusLabel(invite).toLowerCase();
        return (
            invite.email.toLowerCase().includes(term) ||
            invite.role_name.toLowerCase().includes(term) ||
            status.includes(term)
        );
    });
});

const copyInviteLink = async (invite: (typeof props.invites)[number]) => {
    if (!invite.token) {
        return;
    }

    const url = new URL(route('invites.accept', invite.token), window.location.origin).toString();

    if (!('clipboard' in navigator)) {
        copyNotice.value = 'Clipboard not available.';
        window.setTimeout(() => {
            copyNotice.value = null;
        }, 2000);
        return;
    }

    try {
        await navigator.clipboard.writeText(url);
        copyNotice.value = 'Invite link copied.';
    } catch (error) {
        copyNotice.value = 'Unable to copy invite link.';
    }

    window.setTimeout(() => {
        copyNotice.value = null;
    }, 2000);
};

const openUserLink = (invite: (typeof props.invites)[number], anchor = true) => {
    const baseUrl = route('users.index', {
        search: invite.email,
        focus: invite.user_id ?? undefined,
    });

    if (anchor && invite.user_id) {
        return `${baseUrl}#user-${invite.user_id}`;
    }

    return baseUrl;
};

const deactivateUser = (invite: (typeof props.invites)[number]) => {
    if (!invite.user_id) {
        return;
    }

    router.patch(
        route('users.status', invite.user_id),
        { is_active: false },
        { preserveScroll: true },
    );
};

const showCopyLink = (invite: (typeof props.invites)[number]) =>
    !invite.used_at && !invite.revoked_at;
const showResend = (invite: (typeof props.invites)[number]) => !invite.used_at;
const showRevoke = (invite: (typeof props.invites)[number]) =>
    !invite.used_at && !invite.revoked_at;
const showViewProfile = (invite: (typeof props.invites)[number]) =>
    invite.used_at && !!invite.user_id;
const showChangeRole = (invite: (typeof props.invites)[number]) =>
    invite.used_at && !!invite.user_id && canManageUsers.value;
const showRemoveUser = (invite: (typeof props.invites)[number]) =>
    invite.used_at &&
    !!invite.user_id &&
    !!invite.user_is_active &&
    canManageUsers.value;
const hasMenuActions = (invite: (typeof props.invites)[number]) =>
    showCopyLink(invite) ||
    showResend(invite) ||
    showRevoke(invite) ||
    showViewProfile(invite) ||
    showChangeRole(invite) ||
    showRemoveUser(invite);
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
                    @submit.prevent="
                        canInvite ? form.post(route('invites.store')) : null
                    "
                >
                    <div class="grid gap-5 md:grid-cols-[minmax(260px,420px)_280px_auto] md:items-end">
                        <div>
                            <InputLabel for="email" value="Email" class="text-gray-800" />
                            <TextInput
                                id="email"
                                v-model="form.email"
                                type="email"
                                class="mt-1 block w-full border-gray-400 focus:border-gray-900 focus:ring-1 focus:ring-gray-900"
                                autocomplete="off"
                            />
                            <InputError class="mt-2" :message="form.errors.email" />
                        </div>
                        <div>
                            <InputLabel for="role_name" value="Role" class="text-gray-800" />
                            <select
                                id="role_name"
                                v-model="form.role_name"
                                class="mt-1 block w-full rounded-md border-gray-400 shadow-sm focus:border-gray-900 focus:ring-1 focus:ring-gray-900"
                            >
                                <option v-for="role in props.roles" :key="role" :value="role">
                                    {{ role }}
                                </option>
                            </select>
                            <InputError class="mt-2" :message="form.errors.role_name" />
                        </div>
                        <div class="flex items-end">
                            <PrimaryButton v-if="canInvite" :disabled="form.processing">
                                <svg
                                    class="mr-2 h-4 w-4"
                                    viewBox="0 0 20 20"
                                    fill="currentColor"
                                    aria-hidden="true"
                                >
                                    <path d="M2.94 2.34a.75.75 0 01.82-.16l14 6a.75.75 0 010 1.36l-14 6a.75.75 0 01-1.03-.83l1.5-5.24a.75.75 0 01.72-.54h5.65a.75.75 0 000-1.5H5.0a.75.75 0 01-.72-.54l-1.5-5.24a.75.75 0 01.16-.64z" />
                                </svg>
                                Send invite
                            </PrimaryButton>
                        </div>
                    </div>
                </form>

                <div class="rounded-lg border border-gray-200 bg-white p-6">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div class="text-lg font-semibold text-gray-900">
                            Recent invites
                        </div>
                        <div class="flex w-full flex-wrap items-center justify-end gap-3 sm:w-auto">
                            <p v-if="copyNotice" class="text-xs text-gray-500" role="status">
                                {{ copyNotice }}
                            </p>
                            <input
                                v-model="tableSearch"
                                type="text"
                                class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-gray-900 focus:outline-none focus:ring-1 focus:ring-gray-900 sm:w-64"
                                placeholder="Search invites..."
                            />
                        </div>
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
                                v-for="invite in filteredInvites"
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
                                            class="rounded-full px-2 py-1 text-xs font-semibold"
                                            :class="statusClass(statusLabel(invite))"
                                        >
                                            {{ statusLabel(invite) }}
                                        </span>
                                    </td>
                                    <td class="py-2 pr-4 text-gray-600">
                                        <span v-if="!invite.used_at" :title="formatDateTime(invite.expires_at)">
                                            {{ formatRelative(invite.expires_at) }}
                                        </span>
                                        <span v-else>-</span>
                                    </td>
                                    <td class="py-2 pr-4 text-gray-600">
                                        <span v-if="invite.used_at" :title="formatDateTime(invite.used_at)">
                                            {{ formatRelative(invite.used_at) }}
                                        </span>
                                        <span v-else>-</span>
                                    </td>
                                    <td class="py-2 pr-4 text-right">
                                        <Dropdown align="right" width="48">
                                            <template #trigger>
                                                <button
                                                    type="button"
                                                    class="rounded-md px-2 py-1 text-sm text-gray-600 hover:bg-gray-100 hover:text-gray-900"
                                                    aria-label="Invite actions"
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
                                                    v-if="showCopyLink(invite)"
                                                    type="button"
                                                    class="block w-full px-4 py-2 text-start text-sm text-gray-700 hover:bg-gray-100"
                                                    @click="copyInviteLink(invite)"
                                                >
                                                    Copy invite link
                                                </button>
                                                <Link
                                                    v-if="showResend(invite)"
                                                    as="button"
                                                    method="post"
                                                    class="block w-full px-4 py-2 text-start text-sm text-gray-700 hover:bg-gray-100"
                                                    :href="route('invites.resend', invite.id)"
                                                >
                                                    Resend
                                                </Link>
                                                <Link
                                                    v-if="showRevoke(invite)"
                                                    as="button"
                                                    method="post"
                                                    class="block w-full px-4 py-2 text-start text-sm text-red-600 hover:bg-gray-100"
                                                    :href="route('invites.revoke', invite.id)"
                                                >
                                                    Revoke
                                                </Link>
                                                <Link
                                                    v-if="showViewProfile(invite)"
                                                    class="block w-full px-4 py-2 text-start text-sm text-gray-700 hover:bg-gray-100"
                                                    :href="openUserLink(invite)"
                                                >
                                                    View profile
                                                </Link>
                                                <Link
                                                    v-if="showChangeRole(invite)"
                                                    class="block w-full px-4 py-2 text-start text-sm text-gray-700 hover:bg-gray-100"
                                                    :href="openUserLink(invite)"
                                                >
                                                    Change role
                                                </Link>
                                                <button
                                                    v-if="showRemoveUser(invite)"
                                                    type="button"
                                                    class="block w-full px-4 py-2 text-start text-sm text-red-600 hover:bg-gray-100"
                                                    @click="deactivateUser(invite)"
                                                >
                                                    Remove user
                                                </button>
                                                <div
                                                    v-if="!hasMenuActions(invite)"
                                                    class="px-4 py-2 text-sm text-gray-500"
                                                >
                                                    No actions available.
                                                </div>
                                            </template>
                                        </Dropdown>
                                    </td>
                                </tr>
                                <tr v-if="!filteredInvites.length">
                                    <td class="py-4 text-sm text-gray-500" colspan="6">
                                        No invites found.
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
