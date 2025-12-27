<?php

namespace App\Services\Invites;

use App\Models\UserInvite;
use App\Models\User;
use Illuminate\Support\Collection;

class ListInvites
{
    public function execute(User $actor): Collection
    {
        $query = UserInvite::query()
            ->latest()
            ->take(50);

        if (! $actor->hasRole('Owner')) {
            $query->where('role_name', 'Sales');
        }

        return $query->get([
            'id',
            'email',
            'role_name',
            'created_by',
            'expires_at',
            'used_at',
            'resent_at',
            'created_at',
        ]);
    }
}
