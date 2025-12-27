<?php

namespace App\Http\Controllers\Invites;

use App\Http\Controllers\Controller;
use App\Http\Requests\AcceptInviteRequest;
use App\Services\Invites\AcceptInvite;
use App\Services\Invites\FindInviteByToken;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class AcceptInviteController extends Controller
{
    public function create(string $token, FindInviteByToken $finder): Response
    {
        $invite = $finder->execute($token);

        return Inertia::render('Invites/Accept', [
            'token' => $token,
            'email' => $invite?->email,
            'status' => $this->statusForInvite($invite),
        ]);
    }

    public function store(AcceptInviteRequest $request, string $token, FindInviteByToken $finder, AcceptInvite $service)
    {
        $invite = $finder->execute($token);
        $status = $this->statusForInvite($invite);

        if ($status !== 'ok') {
            return redirect()->route('invites.accept', $token)
                ->withErrors(['invite' => $status]);
        }

        $user = $service->execute($invite, $request->validated());

        Auth::login($user);

        if ($user->hasAnyRole(['Owner', 'Manager'])) {
            return redirect()->route('dashboard');
        }

        return redirect()->route('invoices.index');
    }

    private function statusForInvite($invite): string
    {
        if (! $invite) {
            return 'invalid';
        }

        if ($invite->used_at) {
            return 'used';
        }

        if ($invite->expires_at->isPast()) {
            return 'expired';
        }

        return 'ok';
    }
}
