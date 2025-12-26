<?php

namespace App\Services\Products;

use App\Models\Product;
use App\Support\BranchContext;

class CreateProduct
{
    public function __construct(private BranchContext $branchContext)
    {
    }

    public function execute(array $data, int $userId): Product
    {
        if (empty($data['track_inventory'])) {
            $data['stock_qty'] = null;
        }

        $data['company_id'] = $this->branchContext->requireCompanyId();
        $data['branch_id'] = $this->branchContext->requireBranchId();
        $data['created_by'] = $userId;

        return Product::create($data);
    }
}
