<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdjustInventoryRequest;
use App\Models\Product;
use App\Services\Audit\AuditLogger;
use App\Services\Inventory\InventoryService;

class InventoryAdjustmentController extends Controller
{
    public function store(
        AdjustInventoryRequest $request,
        Product $product,
        InventoryService $inventoryService,
        AuditLogger $auditLogger
    ) {
        $before = $product->toArray();

        $movement = $inventoryService->adjust(
            $product,
            $request->string('direction'),
            (string) $request->input('quantity'),
            $request->string('reason'),
            $request->user()->id
        );

        $auditLogger->log(
            $request->user()->id,
            'inventory.adjusted',
            'products',
            $product->id,
            $before,
            $product->fresh()->toArray()
        );

        return redirect()->route('products.edit', $product);
    }
}
