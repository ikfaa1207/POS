<?php

namespace App\Models;

use App\Models\Concerns\BelongsToBranch;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentLedger extends Model
{
    use BelongsToBranch, HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'event_uuid',
        'payment_id',
        'invoice_id',
        'company_id',
        'branch_id',
        'terminal_id',
        'amount',
        'method',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
        ];
    }
}
