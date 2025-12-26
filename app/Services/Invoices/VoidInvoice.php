<?php

namespace App\Services\Invoices;

use App\Enums\InvoiceStatus;
use App\Models\Invoice;
use App\Models\Payment;
use App\Services\Audit\AuditLogger;
use App\Services\Inventory\InventoryService;
use App\Services\Payments\ReversePayment;
use App\Services\Periods\FinancialPeriodService;
use Illuminate\Support\Facades\DB;
use App\Events\InvoiceVoided;

class VoidInvoice
{
    public function __construct(
        private InventoryService $inventoryService,
        private InvoiceLockService $lockService,
        private ReversePayment $reversePayment,
        private AuditLogger $auditLogger,
        private FinancialPeriodService $periodService
    ) {
    }

    public function execute(Invoice $invoice, int $lockVersion, int $userId): Invoice
    {
        return DB::transaction(function () use ($invoice, $lockVersion, $userId): Invoice {
            $locked = Invoice::whereKey($invoice->id)->lockForUpdate()->firstOrFail();

            if ($locked->status === InvoiceStatus::Draft) {
                throw new \DomainException('Draft invoices cannot be voided.');
            }

            if ($locked->isVoided()) {
                throw new \DomainException('Invoice is already voided.');
            }

            $this->periodService->assertOpen($locked->branch_id, now());
            $this->lockService->assertLock($locked, $lockVersion);

            $before = $locked->toArray();

            $payments = Payment::where('invoice_id', $locked->id)
                ->where('amount', '>', 0)
                ->get();

            foreach ($payments as $payment) {
                $this->reversePayment->execute($payment, $userId);
            }

            $this->inventoryService->reverseForInvoice($locked, $userId);

            $locked->update([
                'status' => InvoiceStatus::Voided,
                'voided_at' => now(),
            ]);

            $this->lockService->bumpLock($locked, $lockVersion);
            $after = $locked->fresh()->toArray();

            $this->auditLogger->log(
                $userId,
                'invoice.voided',
                'invoices',
                $locked->id,
                $before,
                $after
            );

            $fresh = $locked->fresh(['items', 'payments', 'client']);
            DB::afterCommit(function () use ($fresh, $userId): void {
                event(new InvoiceVoided($fresh, $userId));
            });

            return $fresh;
        });
    }
}
