<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import { useCan } from '@/composables/useCan';
import { useMoney } from '@/composables/useMoney';

const props = defineProps<{
    invoices: Array<{
        id: number;
        number: string;
        client_name: string;
        status: string;
        payment_status: string;
        total_amount: string;
        paid_amount: string;
        balance: string;
    }>;
}>();

const { can } = useCan();
const { formatMoney } = useMoney();
const canCreate = computed(() => can('invoice.create'));
const canView = computed(() => can('invoice.view'));
</script>

<template>
    <Head title="Invoices" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Invoices
                </h2>
                <Link
                    v-if="canCreate"
                    class="rounded-md bg-gray-900 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-800"
                    :href="route('invoices.create')"
                >
                    New invoice
                </Link>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl px-6">
                <div class="rounded-lg border border-gray-200 bg-white p-6">
                    <table class="min-w-full text-left text-sm">
                        <thead class="border-b text-xs uppercase text-gray-500">
                            <tr>
                                <th class="py-2 pr-4">Invoice</th>
                                <th class="py-2 pr-4">Client</th>
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
                                    {{ invoice.client_name }}
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
                                        v-if="canView"
                                        class="text-sm font-medium text-gray-700 hover:text-gray-900"
                                        :href="route('invoices.show', invoice.id)"
                                    >
                                        View
                                    </Link>
                                </td>
                            </tr>
                            <tr v-if="!props.invoices.length">
                                <td class="py-4 text-sm text-gray-500" colspan="7">
                                    No invoices yet.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
