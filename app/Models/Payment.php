<?php

namespace App\Models;

use App\Models\Concerns\BelongsToBranch;
use App\Models\Concerns\HasCreatedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use BelongsToBranch, HasCreatedBy, HasFactory;

    protected $fillable = [
        'invoice_id',
        'method',
        'amount',
        'note',
        'reversal_of_id',
        'company_id',
        'branch_id',
        'terminal_id',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
        ];
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function reversalOf(): BelongsTo
    {
        return $this->belongsTo(Payment::class, 'reversal_of_id');
    }

    protected static function booted(): void
    {
        static::updating(function (): void {
            throw new \DomainException('Payments are immutable.');
        });

        static::deleting(function (): void {
            throw new \DomainException('Payments are immutable.');
        });
    }
}
