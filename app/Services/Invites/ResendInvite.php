<?php

namespace App\Services\Invites;

use App\Mail\InviteUser;
use App\Models\UserInvite;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class ResendInvite
{
    public function execute(UserInvite $invite): UserInvite
    {
        $invite->forceFill([
            'token' => (string) Str::uuid(),
            'expires_at' => now()->addDay(),
            'resent_at' => now(),
        ])->save();

        Mail::to($invite->email)->send(new InviteUser($invite));

        return $invite;
    }
}
