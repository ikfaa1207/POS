<?php

namespace App\Services\Reports;

use App\Enums\InvoiceStatus;
use App\Support\BranchContext;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class SalesByDateRangeReport
{
    public function __construct(private BranchContext $branchContext)
    {
    }

    public function execute(string $startDate, string $endDate): Collection
    {
        $branchId = $this->branchContext->requireBranchId();

        return DB::table('invoices')
            ->where('status', InvoiceStatus::Finalized->value)
            ->where('branch_id', $branchId)
            ->whereBetween('finalized_at', [$startDate.' 00:00:00', $endDate.' 23:59:59'])
            ->groupByRaw('DATE(finalized_at)')
            ->selectRaw('DATE(finalized_at) as sale_date')
            ->selectRaw('COUNT(*) as invoice_count')
            ->selectRaw('SUM(total_amount) as total_sales')
            ->orderBy('sale_date')
            ->get();
    }
}
