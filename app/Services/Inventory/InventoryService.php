<?php

namespace App\Services\Inventory;

use App\Models\InventoryMovement;
use App\Models\Invoice;
use App\Models\Product;
use App\Services\Periods\FinancialPeriodService;
use App\Support\BranchContext;
use App\Support\Money;
use App\Events\InventoryAdjusted;
use Illuminate\Support\Facades\DB;

class InventoryService
{
    public function __construct(
        private BranchContext $branchContext,
        private FinancialPeriodService $periodService
    ) {
    }

    public function deductForInvoice(Invoice $invoice, int $userId): void
    {
        $invoice->loadMissing('items.product');

        foreach ($invoice->items as $item) {
            if (! $item->product || ! $item->product->track_inventory) {
                continue;
            }

            $product = Product::whereKey($item->product_id)->lockForUpdate()->firstOrFail();
            $this->applyMovement(
                $product,
                'out',
                (string) $item->quantity,
                'invoice_finalized',
                $invoice,
                $userId
            );
        }
    }

    public function reverseForInvoice(Invoice $invoice, int $userId): void
    {
        $invoice->loadMissing('items.product');

        foreach ($invoice->items as $item) {
            if (! $item->product || ! $item->product->track_inventory) {
                continue;
            }

            $product = Product::whereKey($item->product_id)->lockForUpdate()->firstOrFail();
            $this->applyMovement(
                $product,
                'in',
                (string) $item->quantity,
                'invoice_voided',
                $invoice,
                $userId
            );
        }
    }

    public function adjust(Product $product, string $direction, string $quantity, string $reason, int $userId): InventoryMovement
    {
        if (! $product->track_inventory) {
            throw new \DomainException('Inventory adjustments require tracked products.');
        }

        $product = Product::whereKey($product->id)->lockForUpdate()->firstOrFail();

        $this->periodService->assertOpen($this->branchContext->requireBranchId(), now());

        return $this->applyMovement($product, $direction, $quantity, $reason, null, $userId);
    }

    private function applyMovement(
        Product $product,
        string $type,
        string $quantity,
        string $reason,
        ?Invoice $invoice,
        int $userId
    ): InventoryMovement {
        $quantity = Money::add('0.00', $quantity);
        $current = Money::add('0.00', (string) ($product->stock_qty ?? '0.00'));

        if ($type === 'out') {
            if (! config('inventory.allow_negative') && $this->isGreaterThan($quantity, $current)) {
                throw new \DomainException('Insufficient stock for this invoice.');
            }

            $newStock = Money::sub($current, $quantity);
        } else {
            $newStock = Money::add($current, $quantity);
        }

        $product->update(['stock_qty' => $newStock]);

        $companyId = $invoice?->company_id ?? $this->branchContext->requireCompanyId();
        $branchId = $invoice?->branch_id ?? $this->branchContext->requireBranchId();
        $terminalId = $invoice?->terminal_id ?? $this->branchContext->terminalId();

        $movement = InventoryMovement::create([
            'product_id' => $product->id,
            'invoice_id' => $invoice?->id,
            'type' => $type,
            'quantity' => $quantity,
            'reason' => $reason,
            'company_id' => $companyId,
            'branch_id' => $branchId,
            'terminal_id' => $terminalId,
            'created_by' => $userId,
            'created_at' => now(),
        ]);

        DB::afterCommit(function () use ($movement, $userId): void {
            event(new InventoryAdjusted($movement, $userId));
        });

        return $movement;
    }

    private function isGreaterThan(string $left, string $right): bool
    {
        if (function_exists('bccomp')) {
            return bccomp($left, $right, 2) === 1;
        }

        return (float) $left > (float) $right;
    }
}
