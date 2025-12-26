<?php

namespace App\Models;

use App\Models\Concerns\BelongsToBranch;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DomainEvent extends Model
{
    use BelongsToBranch, HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'event_uuid',
        'name',
        'payload',
        'company_id',
        'branch_id',
        'terminal_id',
        'user_id',
        'occurred_at',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'occurred_at' => 'datetime',
        ];
    }
}
