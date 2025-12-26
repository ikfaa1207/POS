<?php

namespace App\Services\Products;

use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;

class ListProducts
{
    public function execute(array $filters): LengthAwarePaginator
    {
        $query = Product::query()
            ->select(['id', 'name', 'sku', 'price', 'track_inventory', 'stock_qty', 'deleted_at'])
            ->orderBy('name');

        $search = trim((string) Arr::get($filters, 'search', ''));
        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $builder
                    ->where('name', 'ilike', "%{$search}%")
                    ->orWhere('sku', 'ilike', "%{$search}%");
            });
        }

        if (filter_var(Arr::get($filters, 'inactive'), FILTER_VALIDATE_BOOLEAN)) {
            $query->onlyTrashed();
        }

        if (filter_var(Arr::get($filters, 'track_inventory'), FILTER_VALIDATE_BOOLEAN)) {
            $query->where('track_inventory', true);
        }

        if (filter_var(Arr::get($filters, 'out_of_stock'), FILTER_VALIDATE_BOOLEAN)) {
            $query
                ->where('track_inventory', true)
                ->where(function ($builder) {
                    $builder->whereNull('stock_qty')->orWhere('stock_qty', '<=', 0);
                });
        }

        $perPage = (int) Arr::get($filters, 'per_page', 20);
        $perPage = max(10, min($perPage, 100));

        return $query->paginate($perPage)->withQueryString();
    }
}
