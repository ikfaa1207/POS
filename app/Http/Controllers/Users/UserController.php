<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateUserRoleRequest;
use App\Http\Requests\UpdateUserStatusRequest;
use App\Models\User;
use App\Services\Users\ListManageableRoles;
use App\Services\Users\ListUsers;
use App\Services\Users\SendPasswordReset;
use App\Services\Users\UpdateUserRole;
use App\Services\Users\UpdateUserStatus;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    public function index(ListUsers $users, ListManageableRoles $roles): Response
    {
        return Inertia::render('Users/Index', [
            'users' => $users->execute(request()->user()),
            'roles' => $roles->execute(request()->user()),
        ]);
    }

    public function updateRole(UpdateUserRoleRequest $request, User $user, UpdateUserRole $service)
    {
        $service->execute($request->user(), $user, $request->string('role'));

        return redirect()->route('users.index')
            ->with('success', 'Role updated.');
    }

    public function updateStatus(UpdateUserStatusRequest $request, User $user, UpdateUserStatus $service)
    {
        $service->execute($request->user(), $user, $request->boolean('is_active'));

        return back()
            ->with('success', 'User status updated.');
    }

    public function resetPassword(User $user, SendPasswordReset $service)
    {
        $service->execute(request()->user(), $user);

        return redirect()->route('users.index')
            ->with('success', 'Password reset email sent.');
    }
}
