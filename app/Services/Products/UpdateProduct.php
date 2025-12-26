<?php

namespace App\Services\Products;

use App\Models\Product;

class UpdateProduct
{
    public function execute(Product $product, array $data): Product
    {
        if (array_key_exists('track_inventory', $data) && ! $data['track_inventory']) {
            $data['stock_qty'] = null;
        }

        $product->update($data);

        return $product;
    }
}
