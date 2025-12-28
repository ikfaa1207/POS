<script setup lang="ts">
import Checkbox from '@/Components/Checkbox.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

defineProps<{
    canResetPassword?: boolean;
    status?: string;
}>();

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const showPassword = ref(false);
const passwordType = computed(() => (showPassword.value ? 'text' : 'password'));

const submit = () => {
    form.post(route('login'), {
        onFinish: () => {
            form.reset('password');
        },
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Log in" />

        <template #aside>
            <div class="relative flex h-full w-full items-center justify-center bg-slate-950 text-white">
                <div class="absolute inset-0">
                    <div class="absolute -left-16 top-12 h-72 w-72 rounded-full bg-emerald-500/20 blur-3xl"></div>
                    <div class="absolute right-0 top-1/3 h-64 w-64 rounded-full bg-blue-500/20 blur-3xl"></div>
                    <div class="absolute inset-x-12 bottom-16 h-px bg-gradient-to-r from-transparent via-white/30 to-transparent"></div>
                    <svg class="absolute inset-0 h-full w-full opacity-20" viewBox="0 0 640 640" aria-hidden="true">
                        <path
                            d="M40 480c90-120 170-160 260-140 80 20 120 80 200 60 60-15 90-55 140-120"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                        />
                        <path
                            d="M80 520c100-140 200-200 300-170 90 25 140 110 220 90 50-13 80-45 120-90"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                        />
                    </svg>
                </div>

                <div class="relative z-10 flex h-full w-full flex-col justify-between px-12 py-16">
                    <div>
                        <div class="text-sm font-semibold uppercase tracking-[0.3em] text-emerald-300">
                            Sales Ledger
                        </div>
                        <h1 class="mt-4 max-w-md text-4xl font-semibold leading-tight">
                            <span class="block">Close faster</span>
                            <span class="block">with a ledger you can trust.</span>
                        </h1>
                        <p class="mt-4 max-w-lg text-base text-slate-200">
                            Capture every sale, lock the totals, and keep your team accountable in
                            seconds.
                        </p>
                    </div>

                    <div class="max-w-md rounded-2xl border border-white/20 bg-white/10 p-6 backdrop-blur">
                        <div class="text-xs font-semibold uppercase tracking-wide text-emerald-200">
                            Sales tip of the day
                        </div>
                        <p class="mt-2 text-sm text-slate-100">
                            Confirm the next step before ending a call. It increases close rates by
                            double digits.
                        </p>
                    </div>

                    <div class="text-sm text-slate-200">
                        Join 500+ top-performing sales teams today.
                    </div>
                </div>
            </div>
        </template>

        <div v-if="status" class="mb-4 text-sm font-medium text-green-600">
            {{ status }}
        </div>

        <form @submit.prevent="submit">
            <div class="mb-6">
                <h2 class="text-2xl font-semibold text-slate-900">Welcome back</h2>
                <p class="mt-1 text-sm text-slate-500">
                    Sign in to continue managing sales.
                </p>
            </div>

            <div>
                <InputLabel for="email" value="Email" class="text-slate-700" />

                <TextInput
                    id="email"
                    type="email"
                    class="mt-1 block w-full border-slate-200 bg-slate-50/60 !focus:border-emerald-500 !focus:ring-emerald-500"
                    v-model="form.email"
                    required
                    autofocus
                    autocomplete="username"
                />

                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div class="mt-4">
                <InputLabel for="password" value="Password" class="text-slate-700" />

                <div class="relative mt-1">
                    <TextInput
                        id="password"
                        :type="passwordType"
                        class="block w-full border-slate-200 bg-slate-50/60 pr-10 !focus:border-emerald-500 !focus:ring-emerald-500"
                        v-model="form.password"
                        required
                        autocomplete="current-password"
                    />
                    <button
                        type="button"
                        class="absolute inset-y-0 right-3 flex items-center text-slate-400 hover:text-slate-600"
                        @click="showPassword = !showPassword"
                        aria-label="Toggle password visibility"
                    >
                        <svg
                            v-if="!showPassword"
                            class="h-4 w-4"
                            viewBox="0 0 20 20"
                            fill="currentColor"
                            aria-hidden="true"
                        >
                            <path d="M10 4.5c-4 0-7.3 2.6-9 6.5 1.7 3.9 5 6.5 9 6.5s7.3-2.6 9-6.5c-1.7-3.9-5-6.5-9-6.5zm0 10.5a4 4 0 110-8 4 4 0 010 8z" />
                            <circle cx="10" cy="10" r="2.5" />
                        </svg>
                        <svg
                            v-else
                            class="h-4 w-4"
                            viewBox="0 0 20 20"
                            fill="currentColor"
                            aria-hidden="true"
                        >
                            <path d="M4.03 3.97a.75.75 0 011.06 0l10.94 10.94a.75.75 0 11-1.06 1.06l-1.6-1.6A9.53 9.53 0 0110 16.5c-4 0-7.3-2.6-9-6.5a12.7 12.7 0 013.6-4.8L4.03 3.97z" />
                            <path d="M10 6.5c-.54 0-1.05.11-1.5.31l4.69 4.69A3.5 3.5 0 0010 6.5zM6.9 5.33A4.99 4.99 0 0110 4.5c4 0 7.3 2.6 9 6.5a12.6 12.6 0 01-2.5 3.4l-3.02-3.02a4 4 0 00-6.58-6.59z" />
                        </svg>
                    </button>
                </div>

                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <div class="mt-4 block">
                <label class="flex cursor-pointer items-center gap-2 py-1">
                    <Checkbox name="remember" v-model:checked="form.remember" />
                    <span class="text-sm text-slate-600">Remember me</span>
                </label>
            </div>

            <div class="mt-6 flex items-center justify-between">
                <Link
                    v-if="canResetPassword"
                    :href="route('password.request')"
                    class="rounded-md text-sm text-slate-500 underline hover:text-slate-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2"
                >
                    Forgot your password?
                </Link>

                <PrimaryButton
                    class="ms-4 w-full bg-emerald-600 text-white hover:bg-emerald-500 focus:bg-emerald-600 active:bg-emerald-700 sm:w-auto"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    Log in
                </PrimaryButton>
            </div>
        </form>
    </GuestLayout>
</template>
