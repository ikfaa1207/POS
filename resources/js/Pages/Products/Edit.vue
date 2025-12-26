<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Checkbox from '@/Components/Checkbox.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps<{
    product: {
        id: number;
        name: string;
        sku: string | null;
        description: string | null;
        price: string;
        track_inventory: boolean;
        stock_qty: number | null;
    };
}>();

const form = useForm({
    name: props.product.name,
    sku: props.product.sku ?? '',
    description: props.product.description ?? '',
    price: props.product.price,
    track_inventory: props.product.track_inventory,
    stock_qty: props.product.stock_qty === null ? '' : String(props.product.stock_qty),
});

const adjustForm = useForm({
    direction: 'in',
    quantity: '',
    reason: '',
});
</script>

<template>
    <Head title="Edit Product" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Edit product
                </h2>
                <Link
                    class="text-sm font-medium text-gray-600 hover:text-gray-900"
                    :href="route('products.index')"
                >
                    Back to products
                </Link>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-3xl px-6">
                <form
                    class="rounded-lg border border-gray-200 bg-white p-6"
                    @submit.prevent="form.patch(route('products.update', props.product.id))"
                >
                    <div class="grid gap-6">
                        <div>
                            <InputLabel for="name" value="Product name" />
                            <TextInput
                                id="name"
                                v-model="form.name"
                                type="text"
                                class="mt-1 block w-full"
                                autocomplete="off"
                            />
                            <InputError class="mt-2" :message="form.errors.name" />
                        </div>
                        <div>
                            <InputLabel for="sku" value="SKU" />
                            <TextInput
                                id="sku"
                                v-model="form.sku"
                                type="text"
                                class="mt-1 block w-full"
                                autocomplete="off"
                            />
                            <InputError class="mt-2" :message="form.errors.sku" />
                        </div>
                        <div>
                            <InputLabel for="price" value="Price" />
                            <TextInput
                                id="price"
                                v-model="form.price"
                                type="number"
                                step="0.01"
                                min="0"
                                class="mt-1 block w-full"
                            />
                            <InputError class="mt-2" :message="form.errors.price" />
                        </div>
                        <div class="space-y-3">
                            <label class="flex items-center gap-2 text-sm text-gray-700">
                                <Checkbox v-model:checked="form.track_inventory" />
                                Track inventory
                            </label>
                            <div v-if="form.track_inventory">
                                <InputLabel for="stock_qty" value="Stock quantity" />
                                <TextInput
                                    id="stock_qty"
                                    v-model="form.stock_qty"
                                    type="number"
                                    min="0"
                                    class="mt-1 block w-full"
                                />
                                <InputError class="mt-2" :message="form.errors.stock_qty" />
                            </div>
                        </div>
                        <div>
                            <InputLabel for="description" value="Description" />
                            <textarea
                                id="description"
                                v-model="form.description"
                                rows="4"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            ></textarea>
                            <InputError class="mt-2" :message="form.errors.description" />
                        </div>
                    </div>

                    <div class="mt-6 flex items-center justify-between">
                        <Link
                            class="text-sm font-medium text-red-600 hover:text-red-700"
                            as="button"
                            method="delete"
                            :href="route('products.destroy', props.product.id)"
                        >
                            Delete product
                        </Link>
                        <PrimaryButton :disabled="form.processing">
                            Update product
                        </PrimaryButton>
                    </div>
                </form>

                <form
                    v-if="props.product.track_inventory"
                    class="mt-6 rounded-lg border border-gray-200 bg-white p-6"
                    @submit.prevent="
                        adjustForm.post(route('products.inventory.adjust', props.product.id))
                    "
                >
                    <div class="text-sm font-semibold text-gray-900">
                        Inventory adjustment
                    </div>
                    <div class="mt-4 grid gap-4 md:grid-cols-3">
                        <div>
                            <InputLabel for="direction" value="Direction" />
                            <select
                                id="direction"
                                v-model="adjustForm.direction"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            >
                                <option value="in">Increase</option>
                                <option value="out">Decrease</option>
                            </select>
                        </div>
                        <div>
                            <InputLabel for="quantity" value="Quantity" />
                            <TextInput
                                id="quantity"
                                v-model="adjustForm.quantity"
                                type="number"
                                step="0.01"
                                min="0.01"
                                class="mt-1 block w-full"
                            />
                            <InputError class="mt-2" :message="adjustForm.errors.quantity" />
                        </div>
                        <div>
                            <InputLabel for="reason" value="Reason" />
                            <TextInput
                                id="reason"
                                v-model="adjustForm.reason"
                                type="text"
                                class="mt-1 block w-full"
                            />
                            <InputError class="mt-2" :message="adjustForm.errors.reason" />
                        </div>
                    </div>
                    <div class="mt-4 flex justify-end">
                        <PrimaryButton :disabled="adjustForm.processing">
                            Apply adjustment
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
