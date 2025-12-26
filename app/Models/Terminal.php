<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Terminal extends Model
{
    use HasFactory;

    protected $fillable = [
        'branch_id',
        'name',
        'identifier',
        'is_default_web',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'is_default_web' => 'boolean',
        ];
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }
}
