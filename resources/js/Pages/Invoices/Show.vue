<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed, watch } from 'vue';
import { useCan } from '@/composables/useCan';

const props = defineProps<{
    invoice: {
        id: number;
        number: string;
        status: string;
        finalize_token: string | null;
        lock_version: number;
        total_amount: string;
        finalized_at: string | null;
        voided_at: string | null;
        client: { id: number; name: string };
        items: Array<{
            id: number;
            description: string;
            quantity: string;
            unit_price: string;
            line_total: string;
        }>;
        payments: Array<{
            id: number;
            method: string;
            amount: string;
            note: string | null;
            reversal_of_id: number | null;
            created_at: string;
        }>;
    };
    paidAmount: string;
    balance: string;
    paymentStatus: string;
    products: Array<{
        id: number;
        name: string;
        price: string;
    }>;
}>();

const itemForm = useForm({
    product_id: '',
    description: '',
    quantity: '1',
    unit_price: '',
    lock_version: props.invoice.lock_version,
});

const paymentForm = useForm({
    method: 'cash',
    amount: '',
    note: '',
    lock_version: props.invoice.lock_version,
});

const selectedProduct = computed(() =>
    props.products.find((product) => product.id === Number(itemForm.product_id)),
);

const { can } = useCan();
const canFinalize = computed(() => can('invoice.finalize'));
const canVoid = computed(() => can('invoice.void'));
const canEdit = computed(() => can('invoice.edit'));
const canRecordPayment = computed(() => can('payment.record'));
const canReversePayment = computed(() => can('payment.reverse'));

watch(
    () => itemForm.product_id,
    () => {
        if (selectedProduct.value) {
            itemForm.description = selectedProduct.value.name;
            itemForm.unit_price = selectedProduct.value.price;
        }
    },
);

watch(
    () => props.invoice.lock_version,
    (value) => {
        itemForm.lock_version = value;
        paymentForm.lock_version = value;
    },
);

const formatMoney = (value: string | number) =>
    new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
    }).format(Number(value));

const formatDateTime = (value: string | null) => {
    if (!value) {
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
</script>

<template>
    <Head :title="`Invoice ${props.invoice.number}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Invoice {{ props.invoice.number }}
                </h2>
                <Link
                    class="text-sm font-medium text-gray-600 hover:text-gray-900"
                    :href="route('invoices.index')"
                >
                    Back to invoices
                </Link>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl space-y-6 px-6">
                <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                    <div class="rounded-lg border border-gray-200 bg-white p-6 lg:col-span-2">
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="text-xs uppercase text-gray-500">Client</div>
                                <div class="mt-1 text-lg font-semibold text-gray-900">
                                    {{ props.invoice.client.name }}
                                </div>
                                <div class="mt-1 text-sm text-gray-500">
                                    Status: {{ props.invoice.status }} / {{ props.paymentStatus }}
                                </div>
                            </div>
                            <div v-if="props.invoice.status === 'draft' && canFinalize">
                                <Link
                                    as="button"
                                    method="post"
                                    class="rounded-md bg-gray-900 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-800"
                                    :href="route('invoices.finalize', props.invoice.id)"
                                    :data="{
                                        lock_version: props.invoice.lock_version,
                                        finalize_token: props.invoice.finalize_token,
                                    }"
                                >
                                    Finalize
                                </Link>
                            </div>
                            <div
                                v-else-if="props.invoice.status === 'finalized'"
                                class="flex items-center gap-3"
                            >
                                <div class="text-sm text-gray-500">
                                    Finalized {{ formatDateTime(props.invoice.finalized_at) }}
                                </div>
                                <Link
                                    v-if="canVoid"
                                    as="button"
                                    method="post"
                                    class="rounded-md border border-red-200 px-3 py-2 text-xs font-semibold text-red-600 hover:border-red-300"
                                    :href="route('invoices.void', props.invoice.id)"
                                    :data="{ lock_version: props.invoice.lock_version }"
                                >
                                    Void invoice
                                </Link>
                            </div>
                            <div v-else class="text-sm text-gray-500">
                                Voided {{ formatDateTime(props.invoice.voided_at) }}
                            </div>
                        </div>
                    </div>
                    <div class="rounded-lg border border-gray-200 bg-white p-6">
                        <div class="space-y-2 text-sm text-gray-600">
                            <div class="flex items-center justify-between">
                                <span>Total</span>
                                <span class="font-semibold text-gray-900">
                                    {{ formatMoney(props.invoice.total_amount) }}
                                </span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span>Paid</span>
                                <span class="font-semibold text-gray-900">
                                    {{ formatMoney(props.paidAmount) }}
                                </span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span>Balance</span>
                                <span class="font-semibold text-red-600">
                                    {{ formatMoney(props.balance) }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="rounded-lg border border-gray-200 bg-white p-6">
                    <div class="text-lg font-semibold text-gray-900">Items</div>
                    <div class="mt-4 overflow-x-auto">
                        <table class="min-w-full text-left text-sm">
                            <thead class="border-b text-xs uppercase text-gray-500">
                                <tr>
                                    <th class="py-2 pr-4">Description</th>
                                    <th class="py-2 pr-4">Qty</th>
                                    <th class="py-2 pr-4">Unit price</th>
                                    <th class="py-2 pr-4">Line total</th>
                                    <th class="py-2 pr-4"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="item in props.invoice.items"
                                    :key="item.id"
                                    class="border-b last:border-b-0"
                                >
                                    <td class="py-2 pr-4 text-gray-900">
                                        {{ item.description }}
                                    </td>
                                    <td class="py-2 pr-4">{{ item.quantity }}</td>
                                    <td class="py-2 pr-4">
                                        {{ formatMoney(item.unit_price) }}
                                    </td>
                                    <td class="py-2 pr-4">
                                        {{ formatMoney(item.line_total) }}
                                    </td>
                                    <td class="py-2 pr-4 text-right">
                                        <Link
                                            v-if="props.invoice.status === 'draft' && canEdit"
                                            as="button"
                                            method="delete"
                                            class="text-xs font-medium text-red-600 hover:text-red-700"
                                            :href="route('invoices.items.destroy', [props.invoice.id, item.id])"
                                            :data="{ lock_version: props.invoice.lock_version }"
                                        >
                                            Remove
                                        </Link>
                                    </td>
                                </tr>
                                <tr v-if="!props.invoice.items.length">
                                    <td class="py-4 text-sm text-gray-500" colspan="5">
                                        Add the first item to continue.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <form
                        v-if="props.invoice.status === 'draft' && canEdit"
                        class="mt-6 grid gap-4 border-t pt-6 md:grid-cols-4"
                        @submit.prevent="
                            itemForm.post(route('invoices.items.store', props.invoice.id))
                        "
                    >
                        <div>
                            <InputLabel for="product_id" value="Product" />
                            <select
                                id="product_id"
                                v-model="itemForm.product_id"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            >
                                <option value="">Custom item</option>
                                <option
                                    v-for="product in props.products"
                                    :key="product.id"
                                    :value="product.id"
                                >
                                    {{ product.name }}
                                </option>
                            </select>
                        </div>
                        <div>
                            <InputLabel for="description" value="Description" />
                            <TextInput
                                id="description"
                                v-model="itemForm.description"
                                type="text"
                                class="mt-1 block w-full"
                            />
                            <InputError class="mt-2" :message="itemForm.errors.description" />
                        </div>
                        <div>
                            <InputLabel for="quantity" value="Quantity" />
                            <TextInput
                                id="quantity"
                                v-model="itemForm.quantity"
                                type="number"
                                step="0.01"
                                min="0.01"
                                class="mt-1 block w-full"
                            />
                            <InputError class="mt-2" :message="itemForm.errors.quantity" />
                        </div>
                        <div>
                            <InputLabel for="unit_price" value="Unit price" />
                            <TextInput
                                id="unit_price"
                                v-model="itemForm.unit_price"
                                type="number"
                                step="0.01"
                                min="0"
                                class="mt-1 block w-full"
                            />
                            <InputError class="mt-2" :message="itemForm.errors.unit_price" />
                        </div>
                        <div class="md:col-span-4 flex justify-end">
                            <PrimaryButton :disabled="itemForm.processing">
                                Add item
                            </PrimaryButton>
                        </div>
                    </form>
                </div>

                <div class="rounded-lg border border-gray-200 bg-white p-6">
                    <div class="text-lg font-semibold text-gray-900">Payments</div>
                    <div class="mt-4 overflow-x-auto">
                        <table class="min-w-full text-left text-sm">
                            <thead class="border-b text-xs uppercase text-gray-500">
                                <tr>
                                    <th class="py-2 pr-4">Method</th>
                                    <th class="py-2 pr-4">Amount</th>
                                    <th class="py-2 pr-4">Note</th>
                                    <th class="py-2 pr-4"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="payment in props.invoice.payments"
                                    :key="payment.id"
                                    class="border-b last:border-b-0"
                                >
                                    <td class="py-2 pr-4 text-gray-900">
                                        {{ payment.method }}
                                    </td>
                                    <td class="py-2 pr-4">
                                        {{ formatMoney(payment.amount) }}
                                    </td>
                                    <td class="py-2 pr-4 text-gray-600">
                                        {{ payment.note ?? '—' }}
                                    </td>
                                    <td class="py-2 pr-4 text-right">
                                        <Link
                                            v-if="Number(payment.amount) > 0 && canReversePayment"
                                            as="button"
                                            method="post"
                                            class="text-xs font-medium text-gray-600 hover:text-gray-900"
                                            :href="route('payments.reverse', payment.id)"
                                        >
                                            Reverse
                                        </Link>
                                    </td>
                                </tr>
                                <tr v-if="!props.invoice.payments.length">
                                    <td class="py-4 text-sm text-gray-500" colspan="4">
                                        No payments yet.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <form
                        v-if="props.invoice.status === 'finalized' && canRecordPayment"
                        class="mt-6 grid gap-4 border-t pt-6 md:grid-cols-4"
                        @submit.prevent="
                            paymentForm.post(route('invoices.payments.store', props.invoice.id))
                        "
                    >
                        <div>
                            <InputLabel for="method" value="Method" />
                            <TextInput
                                id="method"
                                v-model="paymentForm.method"
                                type="text"
                                class="mt-1 block w-full"
                            />
                            <InputError class="mt-2" :message="paymentForm.errors.method" />
                        </div>
                        <div>
                            <InputLabel for="amount" value="Amount" />
                            <TextInput
                                id="amount"
                                v-model="paymentForm.amount"
                                type="number"
                                step="0.01"
                                min="0.01"
                                class="mt-1 block w-full"
                            />
                            <InputError class="mt-2" :message="paymentForm.errors.amount" />
                        </div>
                        <div class="md:col-span-2">
                            <InputLabel for="note" value="Note" />
                            <TextInput
                                id="note"
                                v-model="paymentForm.note"
                                type="text"
                                class="mt-1 block w-full"
                            />
                            <InputError class="mt-2" :message="paymentForm.errors.note" />
                        </div>
                        <div class="md:col-span-4 flex justify-end">
                            <PrimaryButton :disabled="paymentForm.processing">
                                Record payment
                            </PrimaryButton>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
