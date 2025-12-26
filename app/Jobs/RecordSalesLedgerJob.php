<?php

namespace App\Jobs;

use App\Models\SalesLedger;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RecordSalesLedgerJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private readonly string $eventUuid,
        private readonly int $invoiceId,
        private readonly int $companyId,
        private readonly int $branchId,
        private readonly int $terminalId,
        private readonly string $totalAmount,
        private readonly string $finalizedAt
    ) {
    }

    public function handle(): void
    {
        SalesLedger::firstOrCreate(
            ['event_uuid' => $this->eventUuid],
            [
                'invoice_id' => $this->invoiceId,
                'company_id' => $this->companyId,
                'branch_id' => $this->branchId,
                'terminal_id' => $this->terminalId,
                'total_amount' => $this->totalAmount,
                'finalized_at' => $this->finalizedAt,
            ]
        );
    }
}
