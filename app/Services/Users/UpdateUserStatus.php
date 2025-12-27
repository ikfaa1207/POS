<?php

namespace App\Services\Users;

use App\Models\User;
use Illuminate\Validation\ValidationException;

class UpdateUserStatus
{
    public function __construct(private InvalidateUserSessions $sessions)
    {
    }

    public function execute(User $actor, User $target, bool $isActive): void
    {
        $this->guardSameCompany($actor, $target);
        $this->guardSelf($actor, $target);
        $this->guardOwner($actor, $target, $isActive);

        $target->forceFill(['is_active' => $isActive])->save();

        if (! $isActive) {
            $this->sessions->execute($target);
        }
    }

    private function guardSelf(User $actor, User $target): void
    {
        if ($actor->is($target)) {
            throw ValidationException::withMessages([
                'user' => 'You cannot change your own status.',
            ]);
        }
    }

    private function guardSameCompany(User $actor, User $target): void
    {
        if ($actor->company_id !== $target->company_id) {
            throw ValidationException::withMessages([
                'user' => 'User is outside your company.',
            ]);
        }
    }

    private function guardOwner(User $actor, User $target, bool $isActive): void
    {
        if (! $actor->hasRole('Owner')) {
            if (! $target->hasRole('Sales')) {
                throw ValidationException::withMessages([
                    'user' => 'You can only change Sales users.',
                ]);
            }
        }

        if ($target->hasRole('Owner') && ! $actor->hasRole('Owner')) {
            throw ValidationException::withMessages([
                'user' => 'You cannot change the owner status.',
            ]);
        }

        if (! $isActive && $target->hasRole('Owner') && $actor->hasRole('Owner')) {
            throw ValidationException::withMessages([
                'user' => 'Owner accounts cannot be deactivated.',
            ]);
        }
    }
}
