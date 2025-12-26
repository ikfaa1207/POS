<?php

namespace App\Models;

use App\Models\Concerns\BelongsToBranch;
use App\Models\Concerns\HasCreatedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryMovement extends Model
{
    use BelongsToBranch, HasCreatedBy, HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'product_id',
        'invoice_id',
        'type',
        'quantity',
        'reason',
        'company_id',
        'branch_id',
        'terminal_id',
        'created_by',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:2',
            'created_at' => 'datetime',
        ];
    }
}
