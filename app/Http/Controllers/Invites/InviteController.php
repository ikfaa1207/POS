<?php

namespace App\Http\Controllers\Invites;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreInviteRequest;
use App\Models\UserInvite;
use App\Services\Invites\CreateInvite;
use App\Services\Invites\ListInvites;
use App\Services\Invites\RevokeInvite;
use App\Services\Invites\ResendInvite;
use App\Services\Users\ListManageableRoles;
use Inertia\Inertia;
use Inertia\Response;

class InviteController extends Controller
{
    public function index(ListInvites $service, ListManageableRoles $roles): Response
    {
        return Inertia::render('Invites/Index', [
            'invites' => $service->execute(request()->user()),
            'roles' => $roles->execute(request()->user()),
        ]);
    }

    public function store(StoreInviteRequest $request, CreateInvite $service)
    {
        $service->execute($request->validated(), $request->user());

        return redirect()->route('invites.index');
    }

    public function resend(UserInvite $invite, ResendInvite $service)
    {
        if ($invite->used_at) {
            return redirect()->route('invites.index')
                ->withErrors(['error' => 'Invite already used.']);
        }

        $service->execute($invite);

        return redirect()->route('invites.index')
            ->with('success', 'Invite resent.');
    }

    public function revoke(UserInvite $invite, RevokeInvite $service)
    {
        if ($invite->used_at) {
            return redirect()->route('invites.index')
                ->withErrors(['error' => 'Invite already used.']);
        }

        if ($invite->revoked_at) {
            return redirect()->route('invites.index')
                ->withErrors(['error' => 'Invite already revoked.']);
        }

        $service->execute($invite);

        return redirect()->route('invites.index')
            ->with('success', 'Invite revoked.');
    }
}
