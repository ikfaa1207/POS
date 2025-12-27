<script setup lang="ts">
import { computed, ref } from 'vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import { Link, usePage } from '@inertiajs/vue3';

const showingNavigationDropdown = ref(false);
const page = usePage();
const roles = computed(() => (page.props.auth.roles as string[]) ?? []);
const canManage = computed(() => roles.value.includes('Owner') || roles.value.includes('Manager'));
const errorMessage = computed(() => (page.props.errors as { error?: string } | undefined)?.error);
const flashSuccess = computed(() => (page.props.flash as { success?: string } | undefined)?.success);
const flashError = computed(() => (page.props.flash as { error?: string } | undefined)?.error);
</script>

<template>
    <div>
        <div class="min-h-screen bg-gray-100">
            <nav
                class="border-b border-gray-100 bg-white"
            >
                <!-- Primary Navigation Menu -->
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="flex h-16 items-center justify-between">
                        <div class="flex items-center">
                            <!-- Logo -->
                            <div class="flex shrink-0 items-center">
                                <Link :href="route('dashboard')">
                                    <ApplicationLogo
                                        class="block h-9 w-auto fill-current text-gray-800"
                                    />
                                </Link>
                            </div>

                            <!-- Navigation Links -->
                            <div
                                class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex sm:items-center"
                            >
                                <NavLink
                                    v-if="canManage"
                                    :href="route('dashboard')"
                                    :active="route().current('dashboard')"
                                >
                                    Dashboard
                                </NavLink>
                                <NavLink
                                    :href="route('clients.index')"
                                    :active="route().current('clients.*')"
                                >
                                    Clients
                                </NavLink>
                                <NavLink
                                    :href="route('invoices.index')"
                                    :active="route().current('invoices.*')"
                                >
                                    Invoices
                                </NavLink>
                                <NavLink
                                    v-if="canManage"
                                    :href="route('products.index')"
                                    :active="route().current('products.*')"
                                >
                                    Products
                                </NavLink>
                                <NavLink
                                    v-if="canManage"
                                    :href="route('reports.index')"
                                    :active="route().current('reports.*')"
                                >
                                    Reports
                                </NavLink>
                                <NavLink
                                    v-if="canManage"
                                    :href="route('invites.index')"
                                    :active="route().current('invites.*')"
                                >
                                    Invites
                                </NavLink>
                                <NavLink
                                    v-if="canManage"
                                    :href="route('users.index')"
                                    :active="route().current('users.*')"
                                >
                                    Users
                                </NavLink>
                            </div>
                        </div>

                        <div class="hidden sm:ms-6 sm:flex sm:items-center">
                            <!-- Settings Dropdown -->
                            <div class="relative ms-3">
                                <Dropdown align="right" width="48">
                                    <template #trigger>
                                        <span class="inline-flex rounded-md">
                                            <button
                                                type="button"
                                                class="inline-flex items-center rounded-md border border-transparent bg-white px-3 py-2 text-sm font-medium leading-4 text-gray-500 transition duration-150 ease-in-out hover:text-gray-700 focus:outline-none"
                                            >
                                                {{ $page.props.auth.user.name }}

                                                <svg
                                                    class="-me-0.5 ms-2 h-4 w-4"
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    viewBox="0 0 20 20"
                                                    fill="currentColor"
                                                >
                                                    <path
                                                        fill-rule="evenodd"
                                                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                        clip-rule="evenodd"
                                                    />
                                                </svg>
                                            </button>
                                        </span>
                                    </template>

                                    <template #content>
                                        <DropdownLink
                                            :href="route('profile.edit')"
                                        >
                                            Profile
                                        </DropdownLink>
                                        <DropdownLink
                                            :href="route('logout')"
                                            method="post"
                                            as="button"
                                        >
                                            Log Out
                                        </DropdownLink>
                                    </template>
                                </Dropdown>
                            </div>
                        </div>

                        <!-- Hamburger -->
                        <div class="-me-2 flex items-center sm:hidden">
                            <button
                                @click="
                                    showingNavigationDropdown =
                                        !showingNavigationDropdown
                                "
                                class="inline-flex items-center justify-center rounded-md p-2 text-gray-400 transition duration-150 ease-in-out hover:bg-gray-100 hover:text-gray-500 focus:bg-gray-100 focus:text-gray-500 focus:outline-none"
                            >
                                <svg
                                    class="h-6 w-6"
                                    stroke="currentColor"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        :class="{
                                            hidden: showingNavigationDropdown,
                                            'inline-flex':
                                                !showingNavigationDropdown,
                                        }"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M4 6h16M4 12h16M4 18h16"
                                    />
                                    <path
                                        :class="{
                                            hidden: !showingNavigationDropdown,
                                            'inline-flex':
                                                showingNavigationDropdown,
                                        }"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12"
                                    />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Responsive Navigation Menu -->
                <div
                    :class="{
                        block: showingNavigationDropdown,
                        hidden: !showingNavigationDropdown,
                    }"
                    class="sm:hidden"
                >
                    <div class="space-y-1 pb-3 pt-2">
                        <ResponsiveNavLink
                            v-if="canManage"
                            :href="route('dashboard')"
                            :active="route().current('dashboard')"
                        >
                            Dashboard
                        </ResponsiveNavLink>
                        <ResponsiveNavLink
                            :href="route('clients.index')"
                            :active="route().current('clients.*')"
                        >
                            Clients
                        </ResponsiveNavLink>
                        <ResponsiveNavLink
                            :href="route('invoices.index')"
                            :active="route().current('invoices.*')"
                        >
                            Invoices
                        </ResponsiveNavLink>
                        <ResponsiveNavLink
                            v-if="canManage"
                            :href="route('products.index')"
                            :active="route().current('products.*')"
                        >
                            Products
                        </ResponsiveNavLink>
                        <ResponsiveNavLink
                            v-if="canManage"
                            :href="route('reports.index')"
                            :active="route().current('reports.*')"
                        >
                            Reports
                        </ResponsiveNavLink>
                        <ResponsiveNavLink
                            v-if="canManage"
                            :href="route('invites.index')"
                            :active="route().current('invites.*')"
                        >
                            Invites
                        </ResponsiveNavLink>
                        <ResponsiveNavLink
                            v-if="canManage"
                            :href="route('users.index')"
                            :active="route().current('users.*')"
                        >
                            Users
                        </ResponsiveNavLink>
                    </div>

                    <!-- Responsive Settings Options -->
                    <div
                        class="border-t border-gray-200 pb-1 pt-4"
                    >
                        <div class="px-4">
                            <div
                                class="text-base font-medium text-gray-800"
                            >
                                {{ $page.props.auth.user.name }}
                            </div>
                            <div class="text-sm font-medium text-gray-500">
                                {{ $page.props.auth.user.email }}
                            </div>
                        </div>

                        <div class="mt-3 space-y-1">
                            <ResponsiveNavLink :href="route('profile.edit')">
                                Profile
                            </ResponsiveNavLink>
                            <ResponsiveNavLink
                                :href="route('logout')"
                                method="post"
                                as="button"
                            >
                                Log Out
                            </ResponsiveNavLink>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Page Heading -->
            <header
                class="bg-white shadow"
                v-if="$slots.header"
            >
                <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
                    <slot name="header" />
                </div>
            </header>

            <!-- Page Content -->
            <main>
                <div v-if="errorMessage" class="mx-auto max-w-7xl px-6 pt-6">
                    <div class="rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                        {{ errorMessage }}
                    </div>
                </div>
                <div v-if="flashError" class="mx-auto max-w-7xl px-6 pt-6">
                    <div class="rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                        {{ flashError }}
                    </div>
                </div>
                <div v-if="flashSuccess" class="mx-auto max-w-7xl px-6 pt-6">
                    <div class="rounded-md border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
                        {{ flashSuccess }}
                    </div>
                </div>
                <slot />
            </main>
        </div>
    </div>
</template>
