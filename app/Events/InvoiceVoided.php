<?php

namespace App\Events;

use App\Models\Invoice;
use Illuminate\Support\Str;

class InvoiceVoided extends BaseDomainEvent
{
    public function __construct(Invoice $invoice, int $userId)
    {
        parent::__construct(
            eventUuid: (string) Str::uuid(),
            eventName: 'invoice.voided',
            eventPayload: [
                'invoice_id' => $invoice->id,
                'number' => $invoice->number,
                'voided_at' => $invoice->voided_at?->toDateTimeString(),
            ],
            companyId: $invoice->company_id,
            branchId: $invoice->branch_id,
            terminalId: $invoice->terminal_id,
            userId: $userId,
            occurredAt: $invoice->voided_at ?? now()
        );
    }
}
