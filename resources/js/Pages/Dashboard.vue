<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';

const props = defineProps<{
    todaySales: string;
    outstandingInvoices: Array<{
        id: number;
        number: string;
        client_id: number;
        total_amount: string;
        paid_amount: string;
        balance: string;
        finalized_at: string | null;
    }>;
    salesByProduct: Array<{
        product_name: string;
        quantity_sold: string;
        total_sales: string;
    }>;
}>();

const formatMoney = (value: string | number) =>
    new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
    }).format(Number(value));
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Dashboard
            </h2>
        </template>

        <div class="py-10">
            <div class="mx-auto max-w-7xl space-y-6 px-6">
                <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                    <div class="rounded-lg border border-gray-200 bg-white p-6">
                        <div class="text-sm font-medium text-gray-500">
                            Today's sales
                        </div>
                        <div class="mt-2 text-2xl font-semibold text-gray-900">
                            {{ formatMoney(props.todaySales) }}
                        </div>
                    </div>
                    <div class="rounded-lg border border-gray-200 bg-white p-6">
                        <div class="text-sm font-medium text-gray-500">
                            Outstanding invoices
                        </div>
                        <div class="mt-2 text-2xl font-semibold text-gray-900">
                            {{ props.outstandingInvoices.length }}
                        </div>
                    </div>
                    <div class="rounded-lg border border-gray-200 bg-white p-6">
                        <div class="text-sm font-medium text-gray-500">
                            Products sold today
                        </div>
                        <div class="mt-2 text-2xl font-semibold text-gray-900">
                            {{ props.salesByProduct.length }}
                        </div>
                    </div>
                </div>

                <div class="rounded-lg border border-gray-200 bg-white p-6">
                    <div class="text-lg font-semibold text-gray-900">
                        Outstanding balances
                    </div>
                    <div class="mt-4 overflow-x-auto">
                        <table class="min-w-full text-left text-sm">
                            <thead class="border-b text-xs uppercase text-gray-500">
                                <tr>
                                    <th class="py-2 pr-4">Invoice</th>
                                    <th class="py-2 pr-4">Total</th>
                                    <th class="py-2 pr-4">Paid</th>
                                    <th class="py-2 pr-4">Balance</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="invoice in props.outstandingInvoices"
                                    :key="invoice.id"
                                    class="border-b last:border-b-0"
                                >
                                    <td class="py-2 pr-4 text-gray-900">
                                        {{ invoice.number }}
                                    </td>
                                    <td class="py-2 pr-4">
                                        {{ formatMoney(invoice.total_amount) }}
                                    </td>
                                    <td class="py-2 pr-4">
                                        {{ formatMoney(invoice.paid_amount) }}
                                    </td>
                                    <td class="py-2 pr-4 font-medium text-red-600">
                                        {{ formatMoney(invoice.balance) }}
                                    </td>
                                </tr>
                                <tr v-if="!props.outstandingInvoices.length">
                                    <td class="py-4 text-sm text-gray-500" colspan="4">
                                        No outstanding balances.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="rounded-lg border border-gray-200 bg-white p-6">
                    <div class="text-lg font-semibold text-gray-900">
                        Sales by product
                    </div>
                    <div class="mt-4 overflow-x-auto">
                        <table class="min-w-full text-left text-sm">
                            <thead class="border-b text-xs uppercase text-gray-500">
                                <tr>
                                    <th class="py-2 pr-4">Product</th>
                                    <th class="py-2 pr-4">Qty</th>
                                    <th class="py-2 pr-4">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="row in props.salesByProduct"
                                    :key="row.product_name"
                                    class="border-b last:border-b-0"
                                >
                                    <td class="py-2 pr-4 text-gray-900">
                                        {{ row.product_name }}
                                    </td>
                                    <td class="py-2 pr-4">
                                        {{ row.quantity_sold }}
                                    </td>
                                    <td class="py-2 pr-4 font-medium">
                                        {{ formatMoney(row.total_sales) }}
                                    </td>
                                </tr>
                                <tr v-if="!props.salesByProduct.length">
                                    <td class="py-4 text-sm text-gray-500" colspan="3">
                                        No finalized sales yet.
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
