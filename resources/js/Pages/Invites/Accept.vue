<script setup lang="ts">
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';

const props = defineProps<{
    token: string;
    email: string | null;
    status: 'ok' | 'invalid' | 'used' | 'expired';
}>();

const errors = usePage().props.errors as { invite?: string } | undefined;

const form = useForm({
    name: '',
    password: '',
    password_confirmation: '',
});
</script>

<template>
    <GuestLayout>
        <Head title="Accept invite" />

        <div class="mb-4 text-sm text-gray-600">
            Complete your account to join.
        </div>

        <div v-if="props.status !== 'ok'" class="mb-4 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            <span v-if="props.status === 'invalid'">This invite link is invalid.</span>
            <span v-else-if="props.status === 'used'">This invite has already been used.</span>
            <span v-else> This invite has expired.</span>
        </div>
        <div v-else-if="errors?.invite" class="mb-4 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            This invite is no longer valid.
        </div>

        <form
            v-if="props.status === 'ok'"
            @submit.prevent="form.post(route('invites.accept.store', props.token))"
        >
            <div>
                <InputLabel for="email" value="Email" />
                <TextInput
                    id="email"
                    type="email"
                    class="mt-1 block w-full"
                    :model-value="props.email ?? ''"
                    disabled
                />
            </div>

            <div class="mt-4">
                <InputLabel for="name" value="Name" />
                <TextInput
                    id="name"
                    v-model="form.name"
                    type="text"
                    class="mt-1 block w-full"
                    autocomplete="name"
                    required
                />
                <InputError class="mt-2" :message="form.errors.name" />
            </div>

            <div class="mt-4">
                <InputLabel for="password" value="Password" />
                <TextInput
                    id="password"
                    v-model="form.password"
                    type="password"
                    class="mt-1 block w-full"
                    autocomplete="new-password"
                    required
                />
                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <div class="mt-4">
                <InputLabel for="password_confirmation" value="Confirm password" />
                <TextInput
                    id="password_confirmation"
                    v-model="form.password_confirmation"
                    type="password"
                    class="mt-1 block w-full"
                    autocomplete="new-password"
                    required
                />
            </div>

            <div class="mt-6">
                <PrimaryButton :disabled="form.processing">
                    Create account
                </PrimaryButton>
            </div>
        </form>
    </GuestLayout>
</template>
