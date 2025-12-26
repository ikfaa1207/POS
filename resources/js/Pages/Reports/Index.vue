<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps<{
    filters: {
        start_date: string;
        end_date: string;
    };
    salesByDate: Array<{
        sale_date: string;
        invoice_count: number;
        total_sales: string;
    }>;
    paymentMismatches: Array<{
        id: number;
        number: string;
        total_amount: string;
        paid_amount: string;
        delta: string;
        finalized_at: string;
    }>;
    inventoryValuation: {
        rows: Array<{
            id: number;
            name: string;
            stock_qty: string;
            price: string;
            stock_value: string;
        }>;
        total: string;
    };
}>();

const form = useForm({
    start_date: props.filters.start_date,
    end_date: props.filters.end_date,
});

const formatDateInput = (date: Date) => {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');

    return `${year}-${month}-${day}`;
};

const applyQuickRange = (range: 'today' | 'last7' | 'last30' | 'mtd') => {
    const today = new Date();
    let start = new Date(today);

    if (range === 'last7') {
        start.setDate(start.getDate() - 6);
    } else if (range === 'last30') {
        start.setDate(start.getDate() - 29);
    } else if (range === 'mtd') {
        start = new Date(today.getFullYear(), today.getMonth(), 1);
    }

    form.start_date = formatDateInput(start);
    form.end_date = formatDateInput(today);
};

const formatMoney = (value: string | number) =>
    new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
    }).format(Number(value));
</script>

<template>
    <Head title="Reports" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Reports
            </h2>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl space-y-6 px-6">
                <form
                    class="rounded-lg border border-gray-200 bg-white p-6"
                    @submit.prevent="form.get(route('reports.index'))"
                >
                    <div class="flex flex-wrap items-end gap-4">
                        <div class="flex flex-wrap items-end gap-3">
                            <div class="w-44">
                                <InputLabel for="start_date" value="Start date" />
                                <TextInput
                                    id="start_date"
                                    v-model="form.start_date"
                                    type="date"
                                    class="mt-1 block w-full"
                                />
                            </div>
                            <div class="pb-2 text-sm font-medium text-gray-400">
                                to
                            </div>
                            <div class="w-44">
                                <InputLabel for="end_date" value="End date" />
                                <TextInput
                                    id="end_date"
                                    v-model="form.end_date"
                                    type="date"
                                    class="mt-1 block w-full"
                                />
                            </div>
                        </div>
                        <div class="flex flex-wrap items-center gap-2 text-sm text-gray-600">
                            <span class="text-xs font-semibold uppercase tracking-wide text-gray-400">
                                Quick
                            </span>
                            <button
                                type="button"
                                class="rounded-md px-2 py-1 hover:bg-gray-100"
                                @click="applyQuickRange('today')"
                            >
                                Today
                            </button>
                            <button
                                type="button"
                                class="rounded-md px-2 py-1 hover:bg-gray-100"
                                @click="applyQuickRange('last7')"
                            >
                                7D
                            </button>
                            <button
                                type="button"
                                class="rounded-md px-2 py-1 hover:bg-gray-100"
                                @click="applyQuickRange('last30')"
                            >
                                30D
                            </button>
                            <button
                                type="button"
                                class="rounded-md px-2 py-1 hover:bg-gray-100"
                                @click="applyQuickRange('mtd')"
                            >
                                MTD
                            </button>
                        </div>
                        <div class="ml-auto flex items-center gap-3">
                            <PrimaryButton :disabled="form.processing">
                                <svg
                                    class="mr-2 h-4 w-4"
                                    viewBox="0 0 20 20"
                                    fill="currentColor"
                                    aria-hidden="true"
                                >
                                    <path
                                        d="M3 3a1 1 0 011-1h12a1 1 0 011 1v14l-4-2-4 2-4-2-4 2V3z"
                                    />
                                </svg>
                                Run report
                            </PrimaryButton>
                            <Link
                                class="text-sm text-gray-600 hover:text-gray-900"
                                :href="route('reports.index')"
                            >
                                Reset
                            </Link>
                        </div>
                    </div>
                </form>

                <div class="rounded-lg border border-gray-200 bg-white p-6">
                    <div class="text-lg font-semibold text-gray-900">
                        Sales by date range
                    </div>
                    <div class="mt-4 overflow-x-auto">
                        <table class="min-w-full text-left text-sm">
                            <thead class="border-b text-xs uppercase text-gray-500">
                                <tr>
                                    <th class="py-2 pr-4">Date</th>
                                    <th class="py-2 pr-4">Invoices</th>
                                    <th class="py-2 pr-4">Total sales</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="row in props.salesByDate"
                                    :key="row.sale_date"
                                    class="border-b last:border-b-0"
                                >
                                    <td class="py-2 pr-4">{{ row.sale_date }}</td>
                                    <td class="py-2 pr-4">{{ row.invoice_count }}</td>
                                    <td class="py-2 pr-4 font-medium">
                                        {{ formatMoney(row.total_sales) }}
                                    </td>
                                </tr>
                                <tr v-if="!props.salesByDate.length">
                                    <td class="py-4 text-sm text-gray-500" colspan="3">
                                        No sales in this range.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="rounded-lg border border-gray-200 bg-white p-6">
                    <div class="text-lg font-semibold text-gray-900">
                        Payments mismatch (overpaid invoices)
                    </div>
                    <div class="mt-4 overflow-x-auto">
                        <table class="min-w-full text-left text-sm">
                            <thead class="border-b text-xs uppercase text-gray-500">
                                <tr>
                                    <th class="py-2 pr-4">Invoice</th>
                                    <th class="py-2 pr-4">Total</th>
                                    <th class="py-2 pr-4">Paid</th>
                                    <th class="py-2 pr-4">Delta</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="row in props.paymentMismatches"
                                    :key="row.id"
                                    class="border-b last:border-b-0"
                                >
                                    <td class="py-2 pr-4">{{ row.number }}</td>
                                    <td class="py-2 pr-4">
                                        {{ formatMoney(row.total_amount) }}
                                    </td>
                                    <td class="py-2 pr-4">
                                        {{ formatMoney(row.paid_amount) }}
                                    </td>
                                    <td class="py-2 pr-4 font-medium text-red-600">
                                        {{ formatMoney(row.delta) }}
                                    </td>
                                </tr>
                                <tr v-if="!props.paymentMismatches.length">
                                    <td class="py-4 text-sm text-gray-500" colspan="4">
                                        No overpayments detected.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="rounded-lg border border-gray-200 bg-white p-6">
                    <div class="text-lg font-semibold text-gray-900">
                        Inventory valuation
                    </div>
                    <div class="mt-4 overflow-x-auto">
                        <table class="min-w-full text-left text-sm">
                            <thead class="border-b text-xs uppercase text-gray-500">
                                <tr>
                                    <th class="py-2 pr-4">Product</th>
                                    <th class="py-2 pr-4">Qty</th>
                                    <th class="py-2 pr-4">Unit price</th>
                                    <th class="py-2 pr-4">Stock value</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="row in props.inventoryValuation.rows"
                                    :key="row.id"
                                    class="border-b last:border-b-0"
                                >
                                    <td class="py-2 pr-4">{{ row.name }}</td>
                                    <td class="py-2 pr-4">{{ row.stock_qty }}</td>
                                    <td class="py-2 pr-4">
                                        {{ formatMoney(row.price) }}
                                    </td>
                                    <td class="py-2 pr-4 font-medium">
                                        {{ formatMoney(row.stock_value) }}
                                    </td>
                                </tr>
                                <tr v-if="!props.inventoryValuation.rows.length">
                                    <td class="py-4 text-sm text-gray-500" colspan="4">
                                        No tracked inventory items.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <div class="mt-4 text-right text-sm text-gray-600">
                            Total: {{ formatMoney(props.inventoryValuation.total) }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
