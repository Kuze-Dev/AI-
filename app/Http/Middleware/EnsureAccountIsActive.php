<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Domain\Auth\Contracts\HasActiveState;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAccountIsActive
{
    public function handle(Request $request, Closure $next, ?string $redirectTo = null): Response
    {
        $user = $request->user();

        if (! $user || ($user instanceof HasActiveState && ! $user->isActive())) {
            return $request->expectsJson()
                ? abort(403, 'Your account is deactivated.')
                : redirect()->guest(route($redirectTo ?: 'activation.notice'));
        }

        return $next($request);
    }
}
