<?php

namespace App\Models;

use App\Models\Concerns\BelongsToBranch;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalesLedger extends Model
{
    use BelongsToBranch, HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'event_uuid',
        'invoice_id',
        'company_id',
        'branch_id',
        'terminal_id',
        'total_amount',
        'finalized_at',
    ];

    protected function casts(): array
    {
        return [
            'total_amount' => 'decimal:2',
            'finalized_at' => 'datetime',
        ];
    }
}
