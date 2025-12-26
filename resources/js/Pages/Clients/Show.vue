<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps<{
    client: {
        id: number;
        name: string;
        contact_name: string | null;
        email: string | null;
        phone: string | null;
        notes: string | null;
        is_walk_in: boolean;
    };
    invoices: Array<{
        id: number;
        number: string;
        status: string;
        payment_status: string;
        total_amount: string;
        paid_amount: string;
        balance: string;
        finalized_at: string | null;
    }>;
}>();

const formatMoney = (value: string | number) =>
    new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
    }).format(Number(value));
</script>

<template>
    <Head :title="`Client ${props.client.name}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    {{ props.client.name }}
                </h2>
                <div class="flex items-center gap-4">
                    <Link
                        class="text-sm font-medium text-gray-600 hover:text-gray-900"
                        :href="route('clients.edit', props.client.id)"
                    >
                        Edit
                    </Link>
                    <Link
                        class="text-sm font-medium text-gray-600 hover:text-gray-900"
                        :href="route('clients.index')"
                    >
                        Back to clients
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl space-y-6 px-6">
                <div class="rounded-lg border border-gray-200 bg-white p-6">
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div>
                            <div class="text-xs uppercase text-gray-500">Contact</div>
                            <div class="mt-1 text-sm text-gray-900">
                                {{ props.client.contact_name ?? '—' }}
                            </div>
                        </div>
                        <div>
                            <div class="text-xs uppercase text-gray-500">Email</div>
                            <div class="mt-1 text-sm text-gray-900">
                                {{ props.client.email ?? '—' }}
                            </div>
                        </div>
                        <div>
                            <div class="text-xs uppercase text-gray-500">Phone</div>
                            <div class="mt-1 text-sm text-gray-900">
                                {{ props.client.phone ?? '—' }}
                            </div>
                        </div>
                        <div>
                            <div class="text-xs uppercase text-gray-500">Type</div>
                            <div class="mt-1 text-sm text-gray-900">
                                {{ props.client.is_walk_in ? 'Walk-in' : 'Registered' }}
                            </div>
                        </div>
                        <div class="md:col-span-2">
                            <div class="text-xs uppercase text-gray-500">Notes</div>
                            <div class="mt-1 text-sm text-gray-900">
                                {{ props.client.notes ?? '—' }}
                            </div>
                        </div>
                    </div>
                </div>

                <div class="rounded-lg border border-gray-200 bg-white p-6">
                    <div class="text-lg font-semibold text-gray-900">
                        Invoice history
                    </div>
                    <div class="mt-4 overflow-x-auto">
                        <table class="min-w-full text-left text-sm">
                            <thead class="border-b text-xs uppercase text-gray-500">
                                <tr>
                                    <th class="py-2 pr-4">Invoice</th>
                                    <th class="py-2 pr-4">Status</th>
                                    <th class="py-2 pr-4">Payment</th>
                                    <th class="py-2 pr-4">Total</th>
                                    <th class="py-2 pr-4">Balance</th>
                                    <th class="py-2 pr-4"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="invoice in props.invoices"
                                    :key="invoice.id"
                                    class="border-b last:border-b-0"
                                >
                                    <td class="py-2 pr-4 font-medium text-gray-900">
                                        {{ invoice.number }}
                                    </td>
                                    <td class="py-2 pr-4">
                                        {{ invoice.status }}
                                    </td>
                                    <td class="py-2 pr-4">
                                        {{ invoice.payment_status }}
                                    </td>
                                    <td class="py-2 pr-4">
                                        {{ formatMoney(invoice.total_amount) }}
                                    </td>
                                    <td class="py-2 pr-4">
                                        {{ formatMoney(invoice.balance) }}
                                    </td>
                                    <td class="py-2 pr-4 text-right">
                                        <Link
                                            class="text-sm font-medium text-gray-700 hover:text-gray-900"
                                            :href="route('invoices.show', invoice.id)"
                                        >
                                            View
                                        </Link>
                                    </td>
                                </tr>
                                <tr v-if="!props.invoices.length">
                                    <td class="py-4 text-sm text-gray-500" colspan="6">
                                        No invoices yet.
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
