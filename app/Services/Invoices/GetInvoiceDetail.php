<?php

namespace App\Services\Invoices;

use App\Models\Invoice;
use App\Support\Money;

class GetInvoiceDetail
{
    public function execute(Invoice $invoice): array
    {
        $invoice->load(['client', 'items.product', 'payments']);

        if (! $invoice->finalize_token && $invoice->status === \App\Enums\InvoiceStatus::Draft) {
            $invoice->update(['finalize_token' => (string) \Illuminate\Support\Str::uuid()]);
        }

        $paid = Money::add('0.00', (string) $invoice->payments()->sum('amount'));
        $balance = Money::sub((string) $invoice->total_amount, $paid);

        return [
            'invoice' => $invoice,
            'paid_amount' => $paid,
            'balance' => $balance,
            'payment_status' => $this->paymentStatus($invoice->status->value, $paid, $balance),
        ];
    }

    private function paymentStatus(string $status, string $paid, string $balance): string
    {
        if ($status === \App\Enums\InvoiceStatus::Voided->value) {
            return 'voided';
        }

        if ($status !== \App\Enums\InvoiceStatus::Finalized->value) {
            return $status;
        }

        if ($this->isZeroOrLess($balance)) {
            return 'paid';
        }

        if ($this->isGreaterThan($paid, '0.00')) {
            return 'partially_paid';
        }

        return $status;
    }

    private function isGreaterThan(string $left, string $right): bool
    {
        if (function_exists('bccomp')) {
            return bccomp($left, $right, 2) === 1;
        }

        return (float) $left > (float) $right;
    }

    private function isZeroOrLess(string $value): bool
    {
        if (function_exists('bccomp')) {
            return bccomp($value, '0.00', 2) <= 0;
        }

        return (float) $value <= 0;
    }
}
