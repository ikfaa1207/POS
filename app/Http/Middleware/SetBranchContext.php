<?php

namespace App\Http\Middleware;

use App\Support\BranchContext;
use Closure;
use Illuminate\Http\Request;

class SetBranchContext
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        if ($user) {
            $context = app(BranchContext::class);
            $context->initializeForUser($user);

            if (! $context->branchId()) {
                abort(403, 'No branch access configured.');
            }
        }

        return $next($request);
    }
}
