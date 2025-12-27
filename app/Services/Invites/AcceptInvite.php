<?php

namespace App\Services\Invites;

use App\Models\User;
use App\Models\UserInvite;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class AcceptInvite
{
    public function execute(UserInvite $invite, array $data): User
    {
        return DB::transaction(function () use ($invite, $data) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $invite->email,
                'password' => Hash::make($data['password']),
                'company_id' => $invite->company_id,
                'current_branch_id' => $invite->branch_id,
            ]);

            $user->assignRole(Role::findOrCreate($invite->role_name));
            $user->branches()->syncWithoutDetaching([$invite->branch_id]);

            $invite->forceFill(['used_at' => now()])->save();

            event(new Registered($user));

            return $user;
        });
    }
}
