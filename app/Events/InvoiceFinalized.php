<?php

namespace App\Events;

use App\Models\Invoice;
use Illuminate\Support\Str;

class InvoiceFinalized extends BaseDomainEvent
{
    public function __construct(Invoice $invoice, int $userId)
    {
        parent::__construct(
            eventUuid: (string) Str::uuid(),
            eventName: 'invoice.finalized',
            eventPayload: [
                'invoice_id' => $invoice->id,
                'number' => $invoice->number,
                'total_amount' => (string) $invoice->total_amount,
                'finalized_at' => $invoice->finalized_at?->toDateTimeString(),
            ],
            companyId: $invoice->company_id,
            branchId: $invoice->branch_id,
            terminalId: $invoice->terminal_id,
            userId: $userId,
            occurredAt: $invoice->finalized_at ?? now()
        );
    }
}
