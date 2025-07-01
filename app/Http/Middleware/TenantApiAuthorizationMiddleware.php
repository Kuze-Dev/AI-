<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Settings\APISettings;
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

            $token = $request->header('Authorization');

            if (app(APISettings::class)->api_key !== $token) {

                return response()->json(['error' => 'Unauthorized'], 401);
            }

        }

        return $next($request);
    }
}
