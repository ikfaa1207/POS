<?php

namespace App\Services\Reports;

use App\Enums\InvoiceStatus;
use App\Models\Invoice;
use Illuminate\Support\Collection;

class OutstandingBalancesReport
{
    public function execute(): Collection
    {
        return Invoice::query()
            ->leftJoin('payments', 'payments.invoice_id', '=', 'invoices.id')
            ->where('invoices.status', InvoiceStatus::Finalized->value)
            ->groupBy('invoices.id')
            ->select('invoices.*')
            ->selectRaw('COALESCE(SUM(payments.amount), 0) as paid_amount')
            ->selectRaw('(invoices.total_amount - COALESCE(SUM(payments.amount), 0)) as balance')
            ->havingRaw('invoices.total_amount > COALESCE(SUM(payments.amount), 0)')
            ->orderByDesc('invoices.finalized_at')
            ->get();
    }
}
