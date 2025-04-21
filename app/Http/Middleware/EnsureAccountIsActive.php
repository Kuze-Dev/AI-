<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Domain\Admin\Models\Admin;
use Domain\Auth\Contracts\HasActiveState;
use Domain\Customer\Models\Customer;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAccountIsActive
{
    public function handle(Request $request, Closure $next, ?string $redirectTo = null): Response
    {
        /** @var Admin|Customer $user */
        $user = $request->user();

        /** @phpstan-ignore booleanNot.alwaysFalse */
        if (! $user || ($user instanceof HasActiveState && ! $user->isActive())) {
            return $request->expectsJson()
                ? abort(403, 'Your account is deactivated.')
                : redirect()->guest(route($redirectTo ?: 'activation.notice'));
        }

        return $next($request);
    }
}
