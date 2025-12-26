<?php

namespace App\Events;

use App\Models\InventoryMovement;
use Illuminate\Support\Str;

class InventoryAdjusted extends BaseDomainEvent
{
    public function __construct(InventoryMovement $movement, int $userId)
    {
        parent::__construct(
            eventUuid: (string) Str::uuid(),
            eventName: 'inventory.adjusted',
            eventPayload: [
                'movement_id' => $movement->id,
                'product_id' => $movement->product_id,
                'invoice_id' => $movement->invoice_id,
                'type' => $movement->type,
                'quantity' => (string) $movement->quantity,
                'reason' => $movement->reason,
            ],
            companyId: $movement->company_id,
            branchId: $movement->branch_id,
            terminalId: $movement->terminal_id,
            userId: $userId,
            occurredAt: $movement->created_at ?? now()
        );
    }
}
