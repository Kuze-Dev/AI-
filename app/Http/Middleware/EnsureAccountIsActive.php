<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Domain\Auth\Contracts\HasActiveState;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Closure;

class EnsureAccountIsActive
{
    public function handle(Request $request, Closure $next, string $redirectTo = null): Response|RedirectResponse
    {
        $user = $request->user();

        if ( ! $user || ($user instanceof HasActiveState && ! $user->isActive())) {
            return $request->expectsJson()
                ? abort(403, 'Your account is deactivated.')
                : redirect()->guest(route($redirectTo ?: 'activation.notice'));
        }

        return $next($request);
    }
}
