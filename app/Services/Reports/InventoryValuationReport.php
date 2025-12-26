<?php

namespace App\Services\Reports;

use App\Support\BranchContext;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class InventoryValuationReport
{
    public function __construct(private BranchContext $branchContext)
    {
    }

    public function execute(): array
    {
        $branchId = $this->branchContext->requireBranchId();

        $rows = DB::table('products')
            ->where('track_inventory', true)
            ->where('branch_id', $branchId)
            ->select('id', 'name', 'stock_qty', 'price')
            ->selectRaw('(COALESCE(stock_qty, 0) * price) as stock_value')
            ->orderByDesc('stock_value')
            ->get();

        $total = (string) $rows->sum('stock_value');

        return [
            'rows' => $rows,
            'total' => $total,
        ];
    }
}
