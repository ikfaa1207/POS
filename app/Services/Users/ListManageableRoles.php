<?php

namespace App\Services\Users;

use App\Models\User;

class ListManageableRoles
{
    public function execute(User $actor): array
    {
        if ($actor->hasRole('Owner')) {
            return ['Owner', 'Manager', 'Sales'];
        }

        return ['Sales'];
    }
}
