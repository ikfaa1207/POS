<?php

namespace App\Models;

use App\Models\Concerns\BelongsToBranch;
use App\Models\Concerns\HasCreatedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserInvite extends Model
{
    use BelongsToBranch, HasCreatedBy, HasFactory;

    protected $fillable = [
        'token',
        'email',
        'role_name',
        'company_id',
        'branch_id',
        'created_by',
        'expires_at',
        'used_at',
        'resent_at',
        'revoked_at',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'used_at' => 'datetime',
            'resent_at' => 'datetime',
            'revoked_at' => 'datetime',
        ];
    }
}
