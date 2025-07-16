<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Domain\Tenant\Exceptions\SuspendTenantException;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TenantApiAuthorizationMiddleware
{
    /**
     * @throws SuspendTenantException
     */
    public function handle(Request $request, Closure $next, ?string $redirectTo = null): Response
    {

        if (config('custom.strict_api')) {

            if ($request->is('api/*')) {

                return app(\Illuminate\Auth\Middleware\Authenticate::class)
                    ->handle($request, function ($request) use ($next) {
                        return $next($request);
                    }, 'sanctum');
            }

            return $next($request);
        }

        return $next($request);
    }
}
