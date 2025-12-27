<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import { useCan } from '@/composables/useCan';

const props = defineProps<{
    clients: Array<{
        id: number;
        name: string;
        email: string | null;
        phone: string | null;
        is_walk_in: boolean;
    }>;
}>();

const { can } = useCan();
const canCreate = computed(() => can('client.create'));
const canUpdate = computed(() => can('client.update'));
</script>

<template>
    <Head title="Clients" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Clients
                </h2>
                <Link
                    v-if="canCreate"
                    class="rounded-md bg-gray-900 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-800"
                    :href="route('clients.create')"
                >
                    New client
                </Link>
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
                                <th class="py-2 pr-4">Phone</th>
                                <th class="py-2 pr-4">Type</th>
                                <th class="py-2 pr-4"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="client in props.clients"
                                :key="client.id"
                                class="border-b last:border-b-0"
                            >
                                <td class="py-2 pr-4 font-medium text-gray-900">
                                    <Link
                                        class="hover:underline"
                                        :href="route('clients.show', client.id)"
                                    >
                                        {{ client.name }}
                                    </Link>
                                </td>
                                <td class="py-2 pr-4 text-gray-600">
                                    {{ client.email ?? '—' }}
                                </td>
                                <td class="py-2 pr-4 text-gray-600">
                                    {{ client.phone ?? '—' }}
                                </td>
                                <td class="py-2 pr-4">
                                    <span
                                        class="rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-700"
                                    >
                                        {{ client.is_walk_in ? 'Walk-in' : 'Registered' }}
                                    </span>
                                </td>
                                <td class="py-2 pr-4 text-right">
                                    <Link
                                        v-if="canUpdate"
                                        class="text-sm font-medium text-gray-700 hover:text-gray-900"
                                        :href="route('clients.edit', client.id)"
                                    >
                                        Edit
                                    </Link>
                                </td>
                            </tr>
                            <tr v-if="!props.clients.length">
                                <td class="py-4 text-sm text-gray-500" colspan="5">
                                    No clients yet.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
