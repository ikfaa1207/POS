<?php

namespace App\Services\Invoices;

use App\Models\Invoice;

class InvoiceLockService
{
    public function assertLock(Invoice $invoice, int $expected): void
    {
        if ($invoice->lock_version !== $expected) {
            throw new \DomainException('Invoice was updated by another user.');
        }
    }

    public function bumpLock(Invoice $invoice, int $expected): void
    {
        $updated = Invoice::whereKey($invoice->id)
            ->where('lock_version', $expected)
            ->update(['lock_version' => $expected + 1]);

        if ($updated === 0) {
            throw new \DomainException('Invoice was updated by another user.');
        }

        $invoice->lock_version = $expected + 1;
    }
}
