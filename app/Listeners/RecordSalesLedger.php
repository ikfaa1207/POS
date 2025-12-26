<?php

namespace App\Listeners;

use App\Events\InvoiceFinalized;
use App\Jobs\RecordSalesLedgerJob;

class RecordSalesLedger
{
    public function handle(InvoiceFinalized $event): void
    {
        RecordSalesLedgerJob::dispatch(
            $event->eventUuid(),
            $event->payload()['invoice_id'],
            $event->companyId(),
            $event->branchId(),
            $event->terminalId(),
            $event->payload()['total_amount'],
            $event->payload()['finalized_at']
        );
    }
}
