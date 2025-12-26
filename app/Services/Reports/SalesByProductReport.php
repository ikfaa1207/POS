<?php

namespace App\Services\Reports;

use App\Enums\InvoiceStatus;
use App\Support\BranchContext;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class SalesByProductReport
{
    public function __construct(private BranchContext $branchContext)
    {
    }

    public function execute(): Collection
    {
        $branchId = $this->branchContext->requireBranchId();

        return DB::table('invoice_items')
            ->join('invoices', 'invoice_items.invoice_id', '=', 'invoices.id')
            ->leftJoin('products', 'products.id', '=', 'invoice_items.product_id')
            ->where('invoices.status', InvoiceStatus::Finalized->value)
            ->where('invoices.branch_id', $branchId)
            ->groupByRaw('COALESCE(products.name, invoice_items.description)')
            ->selectRaw('COALESCE(products.name, invoice_items.description) as product_name')
            ->selectRaw('SUM(invoice_items.quantity) as quantity_sold')
            ->selectRaw('SUM(invoice_items.line_total) as total_sales')
            ->orderByDesc('total_sales')
            ->get();
    }
}
