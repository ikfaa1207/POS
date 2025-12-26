<?php

namespace App\Models;

use App\Enums\InvoiceStatus;
use App\Models\Concerns\BelongsToBranch;
use App\Models\Concerns\HasCreatedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    use BelongsToBranch, HasCreatedBy, HasFactory;

    protected $fillable = [
        'number',
        'finalize_token',
        'client_id',
        'status',
        'total_amount',
        'finalized_at',
        'voided_at',
        'lock_version',
        'company_id',
        'branch_id',
        'terminal_id',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'status' => InvoiceStatus::class,
            'total_amount' => 'decimal:2',
            'finalized_at' => 'datetime',
            'voided_at' => 'datetime',
            'lock_version' => 'integer',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function isFinalized(): bool
    {
        return $this->status === InvoiceStatus::Finalized;
    }

    public function isVoided(): bool
    {
        return $this->status === InvoiceStatus::Voided;
    }

    protected static function booted(): void
    {
        static::updating(function (Invoice $invoice): void {
            $originalStatus = $invoice->getOriginal('status');
            if ($originalStatus instanceof InvoiceStatus) {
                $originalStatus = $originalStatus->value;
            }

            if ($originalStatus === InvoiceStatus::Draft->value) {
                return;
            }

            $dirty = array_keys($invoice->getDirty());
            $allowed = ['status', 'voided_at', 'lock_version'];
            $blocked = array_diff($dirty, $allowed);

            if ($blocked) {
                throw new \DomainException('Finalized invoices are immutable.');
            }
        });
    }
}
