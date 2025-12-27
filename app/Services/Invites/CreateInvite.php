<?php

namespace App\Services\Invites;

use App\Mail\InviteUser;
use App\Models\User;
use App\Models\UserInvite;
use App\Support\BranchContext;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class CreateInvite
{
    public function __construct(private BranchContext $context)
    {
    }

    public function execute(array $data, User $creator): UserInvite
    {
        $invite = UserInvite::create([
            'token' => (string) Str::uuid(),
            'email' => $data['email'],
            'role_name' => $data['role_name'],
            'company_id' => $this->context->requireCompanyId(),
            'branch_id' => $this->context->requireBranchId(),
            'created_by' => $creator->id,
            'expires_at' => now()->addDay(),
        ]);

        Mail::to($invite->email)->send(new InviteUser($invite));

        return $invite;
    }
}
