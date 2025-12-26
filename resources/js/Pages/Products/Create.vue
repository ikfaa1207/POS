<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Checkbox from '@/Components/Checkbox.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    name: '',
    sku: '',
    description: '',
    price: '',
    track_inventory: false,
    stock_qty: '',
});
</script>

<template>
    <Head title="New Product" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    New product
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
                    @submit.prevent="form.post(route('products.store'))"
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

                    <div class="mt-6 flex justify-end">
                        <PrimaryButton :disabled="form.processing">
                            Save product
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
