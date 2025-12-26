<?php

namespace App\Services\Invoices;

use App\Models\Client;
use App\Models\Invoice;
use Illuminate\Support\Collection;

class ListClientInvoices
{
    public function execute(Client $client): Collection
    {
        $rows = Invoice::query()
            ->where('invoices.client_id', $client->id)
            ->leftJoin('payments', 'payments.invoice_id', '=', 'invoices.id')
            ->groupBy('invoices.id')
            ->select('invoices.*')
            ->selectRaw('COALESCE(SUM(payments.amount), 0) as paid_amount')
            ->selectRaw('(invoices.total_amount - COALESCE(SUM(payments.amount), 0)) as balance')
            ->orderByDesc('invoices.created_at')
            ->get();

        return $rows->map(function ($invoice) {
            $invoice->payment_status = $this->paymentStatus(
                $invoice->status,
                (string) $invoice->paid_amount,
                (string) $invoice->balance
            );

            return $invoice;
        });
    }

    private function paymentStatus($status, string $paid, string $balance): string
    {
        $statusValue = $status instanceof \BackedEnum ? $status->value : (string) $status;

        if ($statusValue === \App\Enums\InvoiceStatus::Voided->value) {
            return 'voided';
        }

        if ($statusValue !== \App\Enums\InvoiceStatus::Finalized->value) {
            return $statusValue;
        }

        if ($this->isZeroOrLess($balance)) {
            return 'paid';
        }

        if ($this->isGreaterThan($paid, '0.00')) {
            return 'partially_paid';
        }

        return $statusValue;
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
