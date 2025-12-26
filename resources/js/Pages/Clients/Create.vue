<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';

const form = useForm({
    name: '',
    contact_name: '',
    email: '',
    phone: '',
    notes: '',
});
</script>

<template>
    <Head title="New Client" />

    <AuthenticatedLayout>
        <template #header>
            <div class="mx-auto flex max-w-xl items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    New client
                </h2>
                <Link
                    class="text-sm font-medium text-gray-600 hover:text-gray-900"
                    :href="route('clients.index')"
                >
                    Back to clients
                </Link>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-xl px-6">
                <form
                    class="rounded-lg border border-gray-200 bg-white p-6"
                    @submit.prevent="form.post(route('clients.store'))"
                >
                    <div class="grid gap-5">
                        <div>
                            <InputLabel for="name" class="text-gray-800">
                                Client name <span class="text-red-500">*</span>
                            </InputLabel>
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
                            <InputLabel
                                for="contact_name"
                                value="Contact name"
                                class="text-gray-800"
                            />
                            <TextInput
                                id="contact_name"
                                v-model="form.contact_name"
                                type="text"
                                class="mt-1 block w-full"
                                autocomplete="off"
                            />
                            <InputError class="mt-2" :message="form.errors.contact_name" />
                        </div>
                        <div class="grid gap-5 md:grid-cols-2">
                            <div>
                                <InputLabel for="email" value="Email" class="text-gray-800" />
                                <TextInput
                                    id="email"
                                    v-model="form.email"
                                    type="email"
                                    class="mt-1 block w-full"
                                    autocomplete="off"
                                />
                                <InputError class="mt-2" :message="form.errors.email" />
                            </div>
                            <div>
                                <InputLabel for="phone" value="Phone" class="text-gray-800" />
                                <TextInput
                                    id="phone"
                                    v-model="form.phone"
                                    type="text"
                                    class="mt-1 block w-full"
                                    autocomplete="off"
                                />
                                <InputError class="mt-2" :message="form.errors.phone" />
                            </div>
                        </div>
                        <div>
                            <InputLabel for="notes" value="Notes" class="text-gray-800" />
                            <textarea
                                id="notes"
                                v-model="form.notes"
                                rows="4"
                                placeholder="Add any additional details..."
                                class="mt-1 block w-full resize-y rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            ></textarea>
                            <InputError class="mt-2" :message="form.errors.notes" />
                        </div>
                    </div>

                    <div class="mt-6 flex items-center gap-3">
                        <PrimaryButton :disabled="form.processing">
                            Save client
                        </PrimaryButton>
                        <Link
                            class="text-sm font-medium text-gray-600 hover:text-gray-900"
                            :href="route('clients.index')"
                        >
                            Cancel
                        </Link>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
