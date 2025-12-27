<?php

namespace App\Services\Permissions;

use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class ListRolePermissions
{
    public function execute(): array
    {
        $roles = Role::query()
            ->with('permissions')
            ->orderBy('name')
            ->get()
            ->map(fn (Role $role) => [
                'name' => $role->name,
                'permissions' => $role->permissions->pluck('name')->values(),
            ])
            ->values();

        $permissions = Permission::query()
            ->orderBy('name')
            ->pluck('name')
            ->values();

        return [
            'roles' => $roles,
            'permissions' => $permissions,
        ];
    }
}
