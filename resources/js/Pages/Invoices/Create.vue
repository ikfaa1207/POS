<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';
import { useCan } from '@/composables/useCan';

const props = defineProps<{
    clients: Array<{
        id: number;
        name: string;
        is_walk_in: boolean;
    }>;
}>();

const defaultClient = props.clients.find((client) => client.is_walk_in);

const form = useForm({
    client_id: defaultClient?.id ?? '',
});

const { can } = useCan();
const canCreate = computed(() => can('invoice.create'));
const submit = () => {
    if (!canCreate.value) {
        return;
    }

    form.post(route('invoices.store'));
};
</script>

<template>
    <Head title="New Invoice" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    New invoice
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
            <div class="mx-auto max-w-3xl px-6">
                <form
                    class="rounded-lg border border-gray-200 bg-white p-6"
                    @submit.prevent="submit"
                >
                    <div>
                        <InputLabel for="client_id" value="Client" />
                        <select
                            id="client_id"
                            v-model="form.client_id"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        >
                            <option disabled value="">Select a client</option>
                            <option
                                v-for="client in props.clients"
                                :key="client.id"
                                :value="client.id"
                            >
                                {{ client.name }}{{ client.is_walk_in ? ' (Walk-in)' : '' }}
                            </option>
                        </select>
                        <InputError class="mt-2" :message="form.errors.client_id" />
                    </div>

                    <div class="mt-6 flex justify-end">
                        <PrimaryButton v-if="canCreate" :disabled="form.processing">
                            Create draft
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
