<?php

namespace App\Services\Invoices;

use App\Enums\InvoiceStatus;
use App\Models\Invoice;
use App\Services\Audit\AuditLogger;
use App\Services\Inventory\InventoryService;
use App\Services\Invoices\InvoiceLockService;
use App\Services\Periods\FinancialPeriodService;
use App\Support\Money;
use Illuminate\Support\Facades\DB;
use App\Events\InvoiceFinalized;

class FinalizeInvoice
{
    public function __construct(
        private InventoryService $inventoryService,
        private InvoiceLockService $lockService,
        private AuditLogger $auditLogger,
        private FinancialPeriodService $periodService
    ) {
    }

    public function execute(Invoice $invoice, string $finalizeToken, int $lockVersion, int $userId): Invoice
    {
        return DB::transaction(function () use ($invoice, $finalizeToken, $lockVersion, $userId): Invoice {
            $locked = Invoice::whereKey($invoice->id)->lockForUpdate()->firstOrFail();

            if ($locked->isFinalized()) {
                throw new \DomainException('Invoice is already finalized.');
            }

            if ($locked->isVoided()) {
                throw new \DomainException('Voided invoices cannot be finalized.');
            }

            if (! $locked->items()->exists()) {
                throw new \DomainException('Invoice needs at least one item.');
            }

            $this->periodService->assertOpen($locked->branch_id, now());
            $this->lockService->assertLock($locked, $lockVersion);

            if ($locked->finalize_token !== $finalizeToken) {
                throw new \DomainException('Finalize token mismatch.');
            }

            $before = $locked->toArray();

            $this->inventoryService->deductForInvoice($locked, $userId);

            $total = Money::add('0.00', (string) $locked->items()->sum('line_total'));

            $locked->update([
                'status' => InvoiceStatus::Finalized,
                'finalized_at' => now(),
                'total_amount' => $total,
            ]);

            $this->lockService->bumpLock($locked, $lockVersion);
            $after = $locked->fresh()->toArray();

            $this->auditLogger->log(
                $userId,
                'invoice.finalized',
                'invoices',
                $locked->id,
                $before,
                $after
            );

            $fresh = $locked->fresh(['items', 'payments', 'client']);
            DB::afterCommit(function () use ($fresh, $userId): void {
                event(new InvoiceFinalized($fresh, $userId));
            });

            return $fresh;
        });
    }
}
