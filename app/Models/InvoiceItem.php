<?php

namespace App\Models;

use App\Enums\InvoiceStatus;
use App\Models\Concerns\HasCreatedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceItem extends Model
{
    use HasCreatedBy, HasFactory;

    protected $fillable = [
        'invoice_id',
        'product_id',
        'description',
        'quantity',
        'unit_price',
        'line_total',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:2',
            'unit_price' => 'decimal:2',
            'line_total' => 'decimal:2',
        ];
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    protected static function booted(): void
    {
        $guard = function (InvoiceItem $item): void {
            $status = Invoice::whereKey($item->invoice_id)->value('status');
            if ($status instanceof InvoiceStatus) {
                $status = $status->value;
            }

            if ($status !== InvoiceStatus::Draft->value) {
                throw new \DomainException('Invoice items can only change while in draft.');
            }
        };

        static::creating($guard);
        static::updating($guard);
        static::deleting($guard);
    }
}
