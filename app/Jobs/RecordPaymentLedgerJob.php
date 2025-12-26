<?php

namespace App\Jobs;

use App\Models\PaymentLedger;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RecordPaymentLedgerJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private readonly string $eventUuid,
        private readonly int $paymentId,
        private readonly int $invoiceId,
        private readonly int $companyId,
        private readonly int $branchId,
        private readonly int $terminalId,
        private readonly string $amount,
        private readonly string $method
    ) {
    }

    public function handle(): void
    {
        PaymentLedger::firstOrCreate(
            ['event_uuid' => $this->eventUuid],
            [
                'payment_id' => $this->paymentId,
                'invoice_id' => $this->invoiceId,
                'company_id' => $this->companyId,
                'branch_id' => $this->branchId,
                'terminal_id' => $this->terminalId,
                'amount' => $this->amount,
                'method' => $this->method,
            ]
        );
    }
}
