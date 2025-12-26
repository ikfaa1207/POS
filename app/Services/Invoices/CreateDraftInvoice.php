<?php

namespace App\Services\Invoices;

use App\Enums\InvoiceStatus;
use App\Models\Invoice;
use App\Support\BranchContext;
use Illuminate\Support\Str;

class CreateDraftInvoice
{
    public function __construct(private BranchContext $branchContext)
    {
    }

    public function execute(int $clientId, int $userId): Invoice
    {
        return Invoice::create([
            'number' => $this->nextNumber(),
            'finalize_token' => (string) Str::uuid(),
            'client_id' => $clientId,
            'status' => InvoiceStatus::Draft,
            'total_amount' => '0.00',
            'lock_version' => 0,
            'company_id' => $this->branchContext->requireCompanyId(),
            'branch_id' => $this->branchContext->requireBranchId(),
            'terminal_id' => $this->branchContext->requireTerminalId(),
            'created_by' => $userId,
        ]);
    }

    private function nextNumber(): string
    {
        $prefix = now()->format('Ymd');

        do {
            $number = 'INV-'.$prefix.'-'.Str::upper(Str::random(6));
        } while (Invoice::where('number', $number)->exists());

        return $number;
    }
}
