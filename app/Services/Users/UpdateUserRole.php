<?php

namespace App\Services\Users;

use App\Models\User;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;

class UpdateUserRole
{
    public function __construct(private InvalidateUserSessions $sessions)
    {
    }

    public function execute(User $actor, User $target, string $roleName): void
    {
        $this->guardSameCompany($actor, $target);
        $this->guardSelf($actor, $target);
        $this->guardRoleChange($actor, $target, $roleName);

        $before = $target->getRoleNames()->sort()->values()->all();

        $target->syncRoles([Role::findOrCreate($roleName)]);

        $after = $target->getRoleNames()->sort()->values()->all();

        if ($before !== $after) {
            $this->sessions->execute($target);
        }
    }

    private function guardSelf(User $actor, User $target): void
    {
        if ($actor->is($target)) {
            throw ValidationException::withMessages([
                'user' => 'You cannot change your own role.',
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

    private function guardRoleChange(User $actor, User $target, string $roleName): void
    {
        if (! $actor->hasRole('Owner')) {
            if ($target->hasRole('Owner') || $target->hasRole('Manager')) {
                throw ValidationException::withMessages([
                    'user' => 'You cannot change manager or owner roles.',
                ]);
            }

            if ($roleName !== 'Sales') {
                throw ValidationException::withMessages([
                    'user' => 'You can only assign the Sales role.',
                ]);
            }
        }
    }
}
