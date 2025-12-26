<?php

namespace App\Models;

use App\Models\Concerns\BelongsToBranch;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FinancialPeriod extends Model
{
    use BelongsToBranch, HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'branch_id',
        'period_date',
        'closed_at',
        'closed_by',
    ];

    protected function casts(): array
    {
        return [
            'period_date' => 'date',
            'closed_at' => 'datetime',
        ];
    }
}
