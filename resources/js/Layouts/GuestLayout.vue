<script setup lang="ts">
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Link } from '@inertiajs/vue3';
import { computed, useSlots } from 'vue';

const slots = useSlots();
const hasAside = computed(() => !!slots.aside);
</script>

<template>
    <div v-if="hasAside" class="min-h-screen bg-slate-50">
        <div class="grid min-h-screen lg:grid-cols-[3fr_2fr]">
            <div class="relative hidden items-center justify-center overflow-hidden lg:flex">
                <slot name="aside" />
            </div>
            <div class="flex items-center justify-center px-6 py-12">
                <div class="w-full max-w-md">
                    <div class="mb-8 flex items-center gap-3">
                        <Link href="/" class="flex items-center gap-3">
                            <ApplicationLogo class="h-10 w-10 fill-current text-slate-900" />
                            <div class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-500">
                                Sales Ledger
                            </div>
                        </Link>
                    </div>
                    <div class="rounded-2xl bg-white p-8 shadow-xl ring-1 ring-slate-200">
                        <slot />
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div
        v-else
        class="flex min-h-screen flex-col items-center bg-gray-100 pt-6 sm:justify-center sm:pt-0"
    >
        <div>
            <Link href="/" class="flex items-center gap-3">
                <ApplicationLogo class="h-14 w-14 fill-current text-gray-500" />
                <div class="text-sm font-semibold uppercase tracking-[0.2em] text-gray-500">
                    Sales Ledger
                </div>
            </Link>
        </div>

        <div
            class="mt-6 w-full overflow-hidden bg-white px-6 py-4 shadow-md sm:max-w-md sm:rounded-lg"
        >
            <slot />
        </div>
    </div>
</template>
