<?php

namespace App\Services\Payments;

use App\Models\Payment;
use App\Services\Audit\AuditLogger;
use App\Services\Periods\FinancialPeriodService;
use App\Support\Money;
use App\Events\PaymentPosted;
use Illuminate\Support\Facades\DB;

class ReversePayment
{
    public function __construct(
        private AuditLogger $auditLogger,
        private FinancialPeriodService $periodService
    )
    {
    }

    public function execute(Payment $payment, int $userId): Payment
    {
        if ($payment->amount < 0) {
            throw new \DomainException('Reversal payments cannot be reversed.');
        }

        if (Payment::where('reversal_of_id', $payment->id)->exists()) {
            throw new \DomainException('Payment already reversed.');
        }

        $invoice = $payment->invoice()->first();
        if ($invoice) {
            $this->periodService->assertOpen($invoice->branch_id, now());
        }

        $reversal = Payment::create([
            'invoice_id' => $payment->invoice_id,
            'method' => 'reversal',
            'amount' => Money::sub('0.00', (string) $payment->amount),
            'note' => 'Reversal of payment #'.$payment->id,
            'reversal_of_id' => $payment->id,
            'company_id' => $invoice?->company_id ?? $payment->company_id,
            'branch_id' => $invoice?->branch_id ?? $payment->branch_id,
            'terminal_id' => $invoice?->terminal_id ?? $payment->terminal_id,
            'created_by' => $userId,
        ]);

        $this->auditLogger->log(
            $userId,
            'payment.reversed',
            'payments',
            $reversal->id,
            $payment->toArray(),
            $reversal->toArray()
        );

        DB::afterCommit(function () use ($reversal, $invoice, $userId): void {
            event(new PaymentPosted($reversal, $invoice, $userId));
        });

        return $reversal;
    }
}
