<?php

namespace App\Services\Invites;

use App\Models\UserInvite;

class RevokeInvite
{
    public function execute(UserInvite $invite): UserInvite
    {
        $invite->forceFill([
            'revoked_at' => now(),
        ])->save();

        return $invite;
    }
}
