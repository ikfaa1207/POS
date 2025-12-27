<?php

namespace App\Services\Invites;

use App\Models\UserInvite;

class FindInviteByToken
{
    public function execute(string $token): ?UserInvite
    {
        return UserInvite::query()
            ->where('token', $token)
            ->first();
    }
}
