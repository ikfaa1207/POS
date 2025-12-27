<?php

namespace App\Services\Permissions;

use Spatie\Permission\Models\Role;

class UpdateRolePermissions
{
    public function execute(Role $role, array $permissions): void
    {
        if ($role->name === 'Owner' && ! in_array('permissions.manage', $permissions, true)) {
            $permissions[] = 'permissions.manage';
        }

        $role->syncPermissions($permissions);
    }
}
