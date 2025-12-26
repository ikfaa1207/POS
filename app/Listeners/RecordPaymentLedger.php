<?php

namespace App\Listeners;

use App\Events\PaymentPosted;
use App\Jobs\RecordPaymentLedgerJob;

class RecordPaymentLedger
{
    public function handle(PaymentPosted $event): void
    {
        RecordPaymentLedgerJob::dispatch(
            $event->eventUuid(),
            $event->payload()['payment_id'],
            $event->payload()['invoice_id'],
            $event->companyId(),
            $event->branchId(),
            $event->terminalId(),
            $event->payload()['amount'],
            $event->payload()['method']
        );
    }
}
