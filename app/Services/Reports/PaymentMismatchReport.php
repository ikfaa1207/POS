<?php

namespace App\Services\Reports;

use App\Enums\InvoiceStatus;
use App\Support\BranchContext;
use Illuminate\Support\Collection;

class PaymentMismatchReport
{
    public function __construct(private BranchContext $branchContext)
    {
    }

    public function execute(string $startDate, string $endDate): Collection
    {
        $branchId = $this->branchContext->requireBranchId();

        return \DB::table('invoices')
            ->leftJoin('payments', 'payments.invoice_id', '=', 'invoices.id')
            ->where('invoices.status', InvoiceStatus::Finalized->value)
            ->where('invoices.branch_id', $branchId)
            ->whereBetween('invoices.finalized_at', [$startDate.' 00:00:00', $endDate.' 23:59:59'])
            ->groupBy('invoices.id')
            ->select('invoices.id', 'invoices.number', 'invoices.total_amount', 'invoices.finalized_at')
            ->selectRaw('COALESCE(SUM(payments.amount), 0) as paid_amount')
            ->selectRaw('(COALESCE(SUM(payments.amount), 0) - invoices.total_amount) as delta')
            ->havingRaw('COALESCE(SUM(payments.amount), 0) > invoices.total_amount')
            ->orderByDesc('delta')
            ->get();
    }
}
