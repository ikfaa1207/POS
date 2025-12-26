<?php

namespace App\Events;

use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Support\Str;

class PaymentPosted extends BaseDomainEvent
{
    public function __construct(Payment $payment, ?Invoice $invoice, int $userId)
    {
        $invoice ??= $payment->invoice;

        parent::__construct(
            eventUuid: (string) Str::uuid(),
            eventName: 'payment.posted',
            eventPayload: [
                'payment_id' => $payment->id,
                'invoice_id' => $payment->invoice_id,
                'amount' => (string) $payment->amount,
                'method' => $payment->method,
                'reversal_of_id' => $payment->reversal_of_id,
            ],
            companyId: $invoice?->company_id ?? $payment->company_id,
            branchId: $invoice?->branch_id ?? $payment->branch_id,
            terminalId: $invoice?->terminal_id ?? $payment->terminal_id,
            userId: $userId,
            occurredAt: $payment->created_at ?? now()
        );
    }
}
