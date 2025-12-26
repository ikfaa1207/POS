<?php

namespace App\Services\Products;

use App\Models\Product;

class DeleteProduct
{
    public function execute(Product $product): void
    {
        if ($product->invoiceItems()->exists()) {
            throw new \DomainException('Products used on invoices cannot be deleted.');
        }

        if (\App\Models\InventoryMovement::where('product_id', $product->id)->exists()) {
            throw new \DomainException('Products with inventory history cannot be deleted.');
        }

        $product->delete();
    }
}
