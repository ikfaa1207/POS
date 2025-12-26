<?php

namespace App\Services\Payments;

use App\Models\Invoice;
use App\Models\Payment;
use App\Services\Audit\AuditLogger;
use App\Services\Invoices\InvoiceLockService;
use App\Services\Periods\FinancialPeriodService;
use App\Support\Money;
use Illuminate\Support\Facades\DB;
use App\Events\PaymentPosted;

class RecordPayment
{
    public function __construct(
        private InvoiceLockService $lockService,
        private AuditLogger $auditLogger,
        private FinancialPeriodService $periodService
    ) {
    }

    public function execute(Invoice $invoice, array $data, int $userId): Payment
    {
        return DB::transaction(function () use ($invoice, $data, $userId): Payment {
            $locked = Invoice::whereKey($invoice->id)->lockForUpdate()->firstOrFail();
            $this->lockService->assertLock($locked, (int) $data['lock_version']);

            if (! $locked->isFinalized()) {
                throw new \DomainException('Invoice must be finalized before payments.');
            }

            if ($locked->isVoided()) {
                throw new \DomainException('Payments cannot be applied to voided invoices.');
            }

            $this->periodService->assertOpen($locked->branch_id, now());

            $amount = Money::add('0.00', (string) $data['amount']);
            $outstanding = $this->outstanding($locked);

            if ($this->isGreaterThan($amount, $outstanding)) {
                throw new \DomainException('Payment exceeds outstanding balance.');
            }

            $payment = $locked->payments()->create([
                'method' => $data['method'],
                'amount' => $amount,
                'note' => $data['note'] ?? null,
                'company_id' => $locked->company_id,
                'branch_id' => $locked->branch_id,
                'terminal_id' => $locked->terminal_id,
                'created_by' => $userId,
            ]);

            $this->lockService->bumpLock($locked, (int) $data['lock_version']);

            $this->auditLogger->log(
                $userId,
                'payment.posted',
                'payments',
                $payment->id,
                null,
                $payment->toArray()
            );

            DB::afterCommit(function () use ($payment, $locked, $userId): void {
                event(new PaymentPosted($payment, $locked, $userId));
            });

            return $payment;
        });
    }

    private function outstanding(Invoice $invoice): string
    {
        $paid = (string) $invoice->payments()->sum('amount');

        return Money::sub((string) $invoice->total_amount, $paid);
    }

    private function isGreaterThan(string $left, string $right): bool
    {
        if (function_exists('bccomp')) {
            return bccomp($left, $right, 2) === 1;
        }

        return (float) $left > (float) $right;
    }
}
