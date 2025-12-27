<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->web(append: [
            \App\Http\Middleware\RequestId::class,
            \App\Http\Middleware\HandleInertiaRequests::class,
            \App\Http\Middleware\SetBranchContext::class,
            \App\Http\Middleware\EnsureUserIsActive::class,
            \Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets::class,
        ]);

        $middleware->alias([
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (\DomainException $exception, \Illuminate\Http\Request $request) {
            if ($request->expectsJson()) {
                return response()->json(['message' => $exception->getMessage()], 422);
            }

            return back()->withErrors(['error' => $exception->getMessage()]);
        });

        $redirectForPermissions = function (?Illuminate\Contracts\Auth\Authenticatable $user): ?string {
            if (! $user) {
                return null;
            }

            if ($user->can('dashboard.view')) {
                return route('dashboard');
            }

            if ($user->can('invoice.view')) {
                return route('invoices.index');
            }

            if ($user->can('product.view')) {
                return route('products.index');
            }

            if ($user->can('client.view')) {
                return route('clients.index');
            }

            if ($user->can('reports.view')) {
                return route('reports.index');
            }

            return route('profile.edit');
        };

        $handlePermissionRedirect = function (\Throwable $exception, \Illuminate\Http\Request $request) use ($redirectForPermissions) {
            if ($request->expectsJson()) {
                return response()->json(['message' => $exception->getMessage()], 403);
            }

            $target = $redirectForPermissions($request->user());
            if (! $target) {
                return null;
            }

            if ($request->fullUrlIs($target)) {
                return null;
            }

            return redirect($target)->withErrors([
                'error' => 'You do not have permission to access that page.',
            ]);
        };

        $exceptions->render(function (\Spatie\Permission\Exceptions\UnauthorizedException $exception, \Illuminate\Http\Request $request) use ($handlePermissionRedirect) {
            return $handlePermissionRedirect($exception, $request);
        });

        $exceptions->render(function (\Illuminate\Auth\Access\AuthorizationException $exception, \Illuminate\Http\Request $request) use ($handlePermissionRedirect) {
            return $handlePermissionRedirect($exception, $request);
        });

        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException $exception, \Illuminate\Http\Request $request) use ($handlePermissionRedirect) {
            return $handlePermissionRedirect($exception, $request);
        });
    })->create();
