<?php

namespace App\Services\Reports;

use App\Enums\InvoiceStatus;
use App\Models\Invoice;

class TodaySalesReport
{
    public function execute(): string
    {
        $total = (string) Invoice::query()
            ->where('status', InvoiceStatus::Finalized->value)
            ->whereDate('finalized_at', now()->toDateString())
            ->sum('total_amount');

        return \App\Support\Money::add('0.00', $total);
    }
}
