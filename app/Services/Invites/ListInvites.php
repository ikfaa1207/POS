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
            ->leftJoin('users', function ($join) {
                $join->on('users.email', '=', 'user_invites.email')
                    ->on('users.company_id', '=', 'user_invites.company_id');
            })
            ->latest('user_invites.created_at')
            ->take(50);

        if (! $actor->hasRole('Owner')) {
            $query->where('role_name', 'Sales');
        }

        return $query->get([
            'user_invites.id',
            'user_invites.email',
            'user_invites.role_name',
            'user_invites.created_by',
            'user_invites.expires_at',
            'user_invites.used_at',
            'user_invites.resent_at',
            'user_invites.revoked_at',
            'user_invites.created_at',
            'user_invites.token',
            'users.id as user_id',
            'users.is_active as user_is_active',
        ]);
    }
}
