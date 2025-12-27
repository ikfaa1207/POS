<?php

namespace App\Services\Users;

use App\Models\User;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;

class SendPasswordReset
{
    public function execute(User $actor, User $target): void
    {
        $this->guardSameCompany($actor, $target);
        $this->guardOwner($actor, $target);

        if (! $target->is_active) {
            throw ValidationException::withMessages([
                'user' => 'Cannot reset password for an inactive user.',
            ]);
        }

        Password::sendResetLink(['email' => $target->email]);
    }

    private function guardSameCompany(User $actor, User $target): void
    {
        if ($actor->company_id !== $target->company_id) {
            throw ValidationException::withMessages([
                'user' => 'User is outside your company.',
            ]);
        }
    }

    private function guardOwner(User $actor, User $target): void
    {
        if (! $actor->hasRole('Owner') && ! $target->hasRole('Sales')) {
            throw ValidationException::withMessages([
                'user' => 'You can only reset Sales users.',
            ]);
        }

        if ($target->hasRole('Owner') && ! $actor->hasRole('Owner')) {
            throw ValidationException::withMessages([
                'user' => 'You cannot reset the owner password.',
            ]);
        }
    }
}
