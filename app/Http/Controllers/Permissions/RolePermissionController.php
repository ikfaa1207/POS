<?php

namespace App\Http\Controllers\Permissions;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateRolePermissionsRequest;
use App\Services\Permissions\ListRolePermissions;
use App\Services\Permissions\UpdateRolePermissions;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Role;

class RolePermissionController extends Controller
{
    public function index(ListRolePermissions $service): Response
    {
        $data = $service->execute();

        return Inertia::render('Permissions/Index', $data);
    }

    public function update(UpdateRolePermissionsRequest $request, string $role, UpdateRolePermissions $service)
    {
        $service->execute(Role::findByName($role), $request->input('permissions', []));

        return redirect()->route('permissions.index')
            ->with('success', 'Permissions updated.');
    }
}
