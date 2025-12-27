<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { computed, onMounted, onUnmounted, ref, watch } from 'vue';
import { useCan } from '@/composables/useCan';

type RoleRow = {
    name: string;
    permissions: string[];
};

type PermissionGroup = {
    key: string;
    label: string;
    permissions: string[];
};

const props = defineProps<{
    roles: RoleRow[];
    permissions: string[];
}>();

const permissionDescriptions: Record<string, string> = {
    'invoice.finalize': 'Finalize a draft invoice (locks it).',
    'invoice.void': 'Void a finalized invoice (creates reversal).',
    'payment.reverse': 'Reverse a payment (creates reversal record).',
    'product.delete': 'Permanently remove products with no usage.',
    'permissions.manage': 'Full control over role permissions.',
    'user.manage': 'Create, update, or deactivate users.',
    'inventory.adjust': 'Adjust inventory with a reason.',
};

const riskyPermissions = new Set([
    'invoice.void',
    'payment.reverse',
    'product.delete',
    'permissions.manage',
    'user.manage',
    'inventory.adjust',
]);

const actionOrder = [
    'view',
    'create',
    'edit',
    'update',
    'delete',
    'finalize',
    'void',
    'record',
    'reverse',
    'adjust',
    'invite',
    'manage',
];

const actionLabels: Record<string, string> = {
    view: 'View',
    create: 'Create',
    edit: 'Edit',
    update: 'Update',
    delete: 'Delete',
    finalize: 'Finalize',
    void: 'Void',
    record: 'Record',
    reverse: 'Reverse',
    adjust: 'Adjust',
    invite: 'Invite',
    manage: 'Manage',
};

const subjectLabels: Record<string, string> = {
    client: 'clients',
    invoice: 'invoices',
    payment: 'payments',
    product: 'products',
    inventory: 'inventory',
    dashboard: 'dashboard',
    reports: 'reports',
    user: 'users',
    permissions: 'permissions',
};

const formatGroupLabel = (group: string) =>
    group
        .split('_')
        .map((word) => word.charAt(0).toUpperCase() + word.slice(1))
        .join(' ');

const formatPermissionLabel = (permission: string) => {
    const [subject, action] = permission.split('.');
    const subjectLabel = subjectLabels[subject] ?? subject;
    const actionLabel = actionLabels[action] ?? action;

    return `${actionLabel} ${subjectLabel}`;
};

const selectedRole = ref(props.roles[0]?.name ?? '');
const searchTerm = ref('');
const selectedGroup = ref('');

const groupedPermissions = computed<PermissionGroup[]>(() => {
    const groups: Record<string, string[]> = {};

    props.permissions.forEach((permission) => {
        const [group] = permission.split('.', 1);
        const key = group || 'other';

        if (!groups[key]) {
            groups[key] = [];
        }

        groups[key].push(permission);
    });

    return Object.entries(groups)
        .map(([key, permissions]) => ({
            key,
            label: formatGroupLabel(key),
            permissions: permissions.sort((a, b) => {
                const aAction = a.split('.')[1] ?? '';
                const bAction = b.split('.')[1] ?? '';
                const aIndex = actionOrder.indexOf(aAction);
                const bIndex = actionOrder.indexOf(bAction);

                if (aIndex === -1 && bIndex === -1) {
                    return a.localeCompare(b);
                }

                if (aIndex === -1) {
                    return 1;
                }

                if (bIndex === -1) {
                    return -1;
                }

                if (aIndex === bIndex) {
                    return a.localeCompare(b);
                }

                return aIndex - bIndex;
            }),
        }))
        .sort((a, b) => a.label.localeCompare(b.label));
});

const filteredGroups = computed<PermissionGroup[]>(() => {
    const term = searchTerm.value.trim().toLowerCase();

    if (!term) {
        return groupedPermissions.value;
    }

    return groupedPermissions.value
        .map((group) => ({
            ...group,
            permissions: group.permissions.filter((permission) => {
                const label = formatPermissionLabel(permission).toLowerCase();
                return (
                    permission.toLowerCase().includes(term) ||
                    label.includes(term)
                );
            }),
        }))
        .filter((group) => group.permissions.length > 0);
});

const isSearching = computed(() => searchTerm.value.trim().length > 0);
const isDirty = computed(() => form.isDirty);
const suppressRoleWatch = ref(false);
const { can } = useCan();
const canManagePermissions = computed(() => can('permissions.manage'));

const visibleGroups = computed(() => {
    if (isSearching.value) {
        return filteredGroups.value;
    }

    return filteredGroups.value.filter((group) => group.key === selectedGroup.value);
});

const form = useForm({
    permissions: [],
});

const activeRole = computed(() => props.roles.find((role) => role.name === selectedRole.value));

watch(
    () => selectedRole.value,
    (value, old) => {
        if (suppressRoleWatch.value) {
            suppressRoleWatch.value = false;
            return;
        }

        if (value !== old && isDirty.value) {
            const proceed = window.confirm('You have unsaved changes. Switch roles without saving?');
            if (!proceed) {
                suppressRoleWatch.value = true;
                selectedRole.value = old;
                return;
            }
        }

        form.permissions = [...(activeRole.value?.permissions ?? [])];
    },
    { immediate: true },
);

watch(
    () => filteredGroups.value,
    (groups) => {
        if (!groups.length) {
            selectedGroup.value = '';
            return;
        }

        if (!selectedGroup.value || !groups.find((group) => group.key === selectedGroup.value)) {
            selectedGroup.value = groups[0].key;
        }
    },
    { immediate: true },
);

const isChecked = (permission: string) => form.permissions.includes(permission);

const groupState = (group: PermissionGroup) => {
    const total = group.permissions.length;
    const selectedCount = group.permissions.filter((permission) => isChecked(permission)).length;

    return {
        total,
        selectedCount,
        all: selectedCount === total,
        indeterminate: selectedCount > 0 && selectedCount < total,
    };
};

const setIndeterminate = (el: HTMLInputElement | null, group: PermissionGroup) => {
    if (!el) {
        return;
    }

    el.indeterminate = groupState(group).indeterminate;
};

const toggleGroup = (group: PermissionGroup, checked: boolean) => {
    const next = new Set(form.permissions);

    group.permissions.forEach((permission) => {
        if (checked) {
            next.add(permission);
        } else {
            next.delete(permission);
        }
    });

    form.permissions = Array.from(next);
};

const handleGroupClick = (key: string) => {
    if (key === selectedGroup.value) {
        return;
    }

    if (isDirty.value) {
        const proceed = window.confirm('You have unsaved changes. Continue without saving?');
        if (!proceed) {
            return;
        }
    }

    selectedGroup.value = key;
};

const submit = () => {
    if (!selectedRole.value) {
        return;
    }

    if (!canManagePermissions.value) {
        return;
    }

    form.patch(route('permissions.update', selectedRole.value), {
        preserveScroll: true,
    });
};

const handleBeforeUnload = (event: BeforeUnloadEvent) => {
    if (!isDirty.value) {
        return;
    }

    event.preventDefault();
    event.returnValue = '';
};

onMounted(() => {
    window.addEventListener('beforeunload', handleBeforeUnload);
});

onUnmounted(() => {
    window.removeEventListener('beforeunload', handleBeforeUnload);
});
</script>

<template>
    <Head title="Permissions" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Permissions
                </h2>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl px-6">
                <div class="rounded-lg border border-gray-200 bg-white p-6">
                    <div class="mb-6 flex flex-wrap items-end gap-4">
                        <div class="min-w-48">
                            <label class="text-sm font-medium text-gray-700">Role</label>
                            <select
                                v-model="selectedRole"
                                class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-gray-900 focus:outline-none focus:ring-1 focus:ring-gray-900"
                            >
                                <option v-for="role in props.roles" :key="role.name" :value="role.name">
                                    {{ role.name }}
                                </option>
                            </select>
                        </div>
                        <div class="min-w-56 flex-1">
                            <label class="text-sm font-medium text-gray-700">Search</label>
                            <input
                                v-model="searchTerm"
                                type="text"
                                class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-gray-900 focus:outline-none focus:ring-1 focus:ring-gray-900"
                                placeholder="Search permissions..."
                            />
                        </div>
                        <div class="text-sm text-gray-500">
                            Changes apply immediately.
                        </div>
                    </div>

                    <div :class="isSearching ? 'grid gap-6' : 'grid gap-6 lg:grid-cols-[220px_1fr]'">
                        <div v-if="!isSearching" class="space-y-2 lg:sticky lg:top-6 lg:self-start">
                            <div class="text-xs font-semibold uppercase tracking-wide text-gray-400">
                                Categories
                            </div>
                            <button
                                v-for="group in filteredGroups"
                                :key="group.key"
                                type="button"
                                class="flex w-full items-center justify-between rounded-md px-3 py-2 text-left text-sm transition"
                                :class="
                                    group.key === selectedGroup && !isSearching
                                        ? 'bg-gray-900 text-white'
                                        : 'text-gray-800 hover:bg-gray-100'
                                "
                                @click="handleGroupClick(group.key)"
                            >
                                <div class="flex items-center gap-2">
                                    <span
                                        v-if="groupState(group).selectedCount > 0"
                                        class="h-2 w-2 rounded-full"
                                        :class="
                                            groupState(group).all
                                                ? 'bg-green-500'
                                                : 'bg-amber-400'
                                        "
                                    ></span>
                                    <span>{{ group.label }}</span>
                                </div>
                                <span class="text-xs text-gray-400">
                                    {{ groupState(group).selectedCount }}/{{ groupState(group).total }}
                                </span>
                            </button>
                        </div>

                        <form class="flex min-h-[420px] flex-col" @submit.prevent="submit">
                            <div v-if="!visibleGroups.length" class="text-sm text-gray-500">
                                No permissions match your search.
                            </div>
                            <div v-else class="flex-1 space-y-6">
                                <div v-for="group in visibleGroups" :key="group.key" :id="group.key">
                                    <div class="mb-3 flex items-center justify-between">
                                        <div>
                                            <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                                                {{ group.label }}
                                            </div>
                                            <div class="text-xs text-gray-400">
                                                {{ groupState(group).selectedCount }} selected
                                            </div>
                                        </div>
                                        <label class="flex items-center gap-2 text-sm text-gray-700">
                                            <input
                                                :ref="(el) => setIndeterminate(el as HTMLInputElement, group)"
                                                type="checkbox"
                                                class="rounded border-gray-300 text-gray-900 focus:ring-gray-900"
                                                :checked="groupState(group).all"
                                                @change="
                                                    toggleGroup(
                                                        group,
                                                        ($event.target as HTMLInputElement).checked,
                                                    )
                                                "
                                            />
                                            Select all
                                        </label>
                                    </div>
                                    <div class="grid gap-3 md:grid-cols-2 lg:grid-cols-3">
                                        <label
                                            v-for="permission in group.permissions"
                                            :key="permission"
                                            class="flex min-h-[88px] items-start gap-3 rounded-md border px-3 py-2 text-sm transition"
                                            :class="[
                                                isChecked(permission)
                                                    ? 'border-indigo-200 bg-indigo-50 text-gray-900'
                                                    : 'border-gray-200 text-gray-700 hover:bg-gray-50',
                                                riskyPermissions.has(permission)
                                                    ? 'border-red-200 bg-red-50/40'
                                                    : '',
                                            ]"
                                            :title="permissionDescriptions[permission]"
                                        >
                                            <input
                                                v-model="form.permissions"
                                                type="checkbox"
                                                :value="permission"
                                                class="mt-1 rounded border-gray-300 text-gray-900 focus:ring-gray-900"
                                            />
                                            <div>
                                                <div class="font-medium text-gray-900">
                                                    {{ formatPermissionLabel(permission) }}
                                                </div>
                                                <div class="text-xs text-gray-400">
                                                    {{ permission }}
                                                </div>
                                            </div>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-6 flex items-center justify-end gap-3 pt-6">
                                <PrimaryButton v-if="canManagePermissions" :disabled="form.processing">
                                    Save permissions
                                </PrimaryButton>
                                <span class="text-sm text-gray-500">
                                    {{ form.permissions.length }} selected
                                </span>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
