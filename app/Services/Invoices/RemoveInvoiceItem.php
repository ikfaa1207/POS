<?php

namespace App\Services\Invoices;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Services\Invoices\InvoiceLockService;
use App\Support\Money;
use Illuminate\Support\Facades\DB;

class RemoveInvoiceItem
{
    public function __construct(private InvoiceLockService $lockService)
    {
    }

    public function execute(Invoice $invoice, InvoiceItem $item, int $lockVersion): void
    {
        if ($invoice->status !== \App\Enums\InvoiceStatus::Draft) {
            throw new \DomainException('Only draft invoices can be edited.');
        }

        if ($item->invoice_id !== $invoice->id) {
            throw new \DomainException('Item does not belong to this invoice.');
        }

        DB::transaction(function () use ($invoice, $item, $lockVersion): void {
            $invoice = Invoice::whereKey($invoice->id)->lockForUpdate()->firstOrFail();
            $this->lockService->assertLock($invoice, $lockVersion);

            $item->delete();
            $total = Money::add('0.00', (string) $invoice->items()->sum('line_total'));
            $invoice->update(['total_amount' => $total]);

            $this->lockService->bumpLock($invoice, $lockVersion);
        });
    }
}
