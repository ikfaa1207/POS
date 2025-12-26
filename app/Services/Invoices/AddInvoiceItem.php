<?php

namespace App\Services\Invoices;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Services\Invoices\InvoiceLockService;
use App\Support\Money;
use Illuminate\Support\Facades\DB;

class AddInvoiceItem
{
    public function __construct(private InvoiceLockService $lockService)
    {
    }

    public function execute(Invoice $invoice, array $data, int $userId): InvoiceItem
    {
        if ($invoice->status !== \App\Enums\InvoiceStatus::Draft) {
            throw new \DomainException('Only draft invoices can be edited.');
        }

        return DB::transaction(function () use ($invoice, $data, $userId): InvoiceItem {
            $invoice = Invoice::whereKey($invoice->id)->lockForUpdate()->firstOrFail();
            $this->lockService->assertLock($invoice, (int) $data['lock_version']);

            $quantity = (string) $data['quantity'];
            $unitPrice = (string) $data['unit_price'];
            $lineTotal = Money::mul($quantity, $unitPrice);

            $item = $invoice->items()->create([
                'product_id' => $data['product_id'] ?? null,
                'description' => $data['description'],
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'line_total' => $lineTotal,
                'created_by' => $userId,
            ]);

            $this->refreshTotals($invoice);
            $this->lockService->bumpLock($invoice, (int) $data['lock_version']);

            return $item;
        });
    }

    private function refreshTotals(Invoice $invoice): void
    {
        $total = Money::add('0.00', (string) $invoice->items()->sum('line_total'));
        $invoice->update(['total_amount' => $total]);
    }
}
