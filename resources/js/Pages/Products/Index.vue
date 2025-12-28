<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { computed, reactive } from 'vue';
import { useCan } from '@/composables/useCan';
import { useMoney } from '@/composables/useMoney';

type ProductRow = {
    id: number;
    name: string;
    sku: string | null;
    price: string;
    track_inventory: boolean;
    stock_qty: number | null;
    deleted_at: string | null;
};

type PaginationLink = {
    url: string | null;
    label: string;
    active: boolean;
};

const props = defineProps<{
    products: {
        data: ProductRow[];
        links: PaginationLink[];
    };
    filters: {
        search?: string;
        track_inventory?: boolean | string;
        out_of_stock?: boolean | string;
        inactive?: boolean | string;
        per_page?: number | string;
    };
}>();

const filters = reactive({
    search: props.filters.search ?? '',
    track_inventory:
        props.filters.track_inventory === true ||
        props.filters.track_inventory === 'true',
    out_of_stock:
        props.filters.out_of_stock === true ||
        props.filters.out_of_stock === 'true',
    inactive:
        props.filters.inactive === true || props.filters.inactive === 'true',
    per_page: Number(props.filters.per_page ?? 20),
});

const { formatMoney } = useMoney();

const applyFilters = () => {
    router.get(
        route('products.index'),
        { ...filters, page: 1 },
        {
            preserveState: true,
            replace: true,
        },
    );
};

const { can } = useCan();
const canCreate = computed(() => can('product.create'));
const canUpdate = computed(() => can('product.update'));
</script>

<template>
    <Head title="Products" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Products
                </h2>
                <Link
                    v-if="canCreate"
                    class="rounded-md bg-gray-900 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-800"
                    :href="route('products.create')"
                >
                    New product
                </Link>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl px-6">
                <div class="rounded-lg border border-gray-200 bg-white p-6">
                    <form
                        class="mb-6 flex flex-wrap items-end gap-4"
                        @submit.prevent="applyFilters"
                    >
                        <div class="min-w-60 flex-1 max-w-lg">
                            <label class="text-sm font-medium text-gray-700">
                                Search
                            </label>
                            <div class="relative mt-1">
                                <svg
                                    class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400"
                                    viewBox="0 0 20 20"
                                    fill="currentColor"
                                    aria-hidden="true"
                                >
                                    <path
                                        fill-rule="evenodd"
                                        d="M8.5 3a5.5 5.5 0 104.035 9.252l3.106 3.107a.75.75 0 101.06-1.06l-3.107-3.107A5.5 5.5 0 008.5 3zm-4 5.5a4 4 0 118 0 4 4 0 01-8 0z"
                                        clip-rule="evenodd"
                                    />
                                </svg>
                                <input
                                    v-model="filters.search"
                                    type="text"
                                    class="w-full rounded-md border border-gray-300 py-2 pl-9 pr-3 text-sm focus:border-gray-900 focus:outline-none focus:ring-1 focus:ring-gray-900"
                                    placeholder="Name or SKU"
                                />
                            </div>
                        </div>
                        <div class="flex flex-wrap items-center gap-6">
                            <label class="flex items-center gap-2 text-sm text-gray-700">
                                <input
                                    v-model="filters.track_inventory"
                                    type="checkbox"
                                    class="rounded border-gray-300 text-gray-900 focus:ring-gray-900"
                                />
                                Track inventory
                            </label>
                            <label class="flex items-center gap-2 text-sm text-gray-700">
                                <input
                                    v-model="filters.out_of_stock"
                                    type="checkbox"
                                    class="rounded border-gray-300 text-gray-900 focus:ring-gray-900"
                                />
                                Out of stock
                            </label>
                            <label class="flex items-center gap-2 text-sm text-gray-700">
                                <input
                                    v-model="filters.inactive"
                                    type="checkbox"
                                    class="rounded border-gray-300 text-gray-900 focus:ring-gray-900"
                                />
                                Inactive
                            </label>
                            <div class="flex items-center gap-2">
                                <button
                                    type="submit"
                                    class="rounded-md bg-gray-900 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-800"
                                >
                                    Apply
                                </button>
                                <Link
                                    class="text-sm text-gray-600 hover:text-gray-900"
                                    :href="route('products.index')"
                                >
                                    Reset
                                </Link>
                            </div>
                        </div>
                        <div class="ml-auto w-36 lg:border-l lg:border-gray-200 lg:pl-4">
                            <label class="text-sm font-medium text-gray-700">
                                Per page
                            </label>
                            <select
                                v-model="filters.per_page"
                                class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-gray-900 focus:outline-none focus:ring-1 focus:ring-gray-900"
                            >
                                <option :value="20">20</option>
                                <option :value="50">50</option>
                                <option :value="100">100</option>
                            </select>
                        </div>
                    </form>

                    <table class="min-w-full text-left text-sm">
                        <thead class="border-b text-xs uppercase text-gray-500">
                            <tr>
                                <th class="py-2 pr-4">Name</th>
                                <th class="py-2 pr-4">SKU</th>
                                <th class="py-2 pr-4">Price</th>
                                <th class="py-2 pr-4">Inventory</th>
                                <th class="py-2 pr-4">Status</th>
                                <th class="py-2 pr-4"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="product in props.products.data"
                                :key="product.id"
                                class="border-b last:border-b-0"
                            >
                                <td class="py-2 pr-4 font-medium text-gray-900">
                                    {{ product.name }}
                                </td>
                                <td class="py-2 pr-4 text-gray-600">
                                    {{ product.sku ?? '-' }}
                                </td>
                                <td class="py-2 pr-4">
                                    {{ formatMoney(product.price) }}
                                </td>
                                <td class="py-2 pr-4 text-gray-600">
                                    <span v-if="product.track_inventory">
                                        {{ product.stock_qty ?? 0 }}
                                    </span>
                                    <span v-else>Not tracked</span>
                                </td>
                                <td class="py-2 pr-4">
                                    <span
                                        v-if="product.deleted_at"
                                        class="rounded-full bg-gray-100 px-2 py-1 text-xs font-medium text-gray-600"
                                    >
                                        Inactive
                                    </span>
                                    <span v-else class="text-xs text-gray-500">
                                        Active
                                    </span>
                                </td>
                                <td class="py-2 pr-4 text-right">
                                    <Link
                                        v-if="canUpdate"
                                        class="text-sm font-medium text-gray-700 hover:text-gray-900"
                                        :href="route('products.edit', product.id)"
                                    >
                                        Edit
                                    </Link>
                                </td>
                            </tr>
                            <tr v-if="!props.products.data.length">
                                <td class="py-4 text-sm text-gray-500" colspan="6">
                                    No products yet.
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <div
                        v-if="props.products.links.length > 3"
                        class="mt-6 flex flex-wrap items-center justify-center gap-2"
                    >
                        <template v-for="link in props.products.links" :key="link.label">
                            <span
                                v-if="!link.url"
                                class="rounded-md border border-gray-200 px-3 py-1 text-sm text-gray-400"
                                v-html="link.label"
                            />
                            <Link
                                v-else
                                class="rounded-md border px-3 py-1 text-sm"
                                :class="
                                    link.active
                                        ? 'border-gray-900 bg-gray-900 text-white'
                                        : 'border-gray-200 text-gray-700 hover:border-gray-300'
                                "
                                :href="link.url"
                                v-html="link.label"
                            />
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
