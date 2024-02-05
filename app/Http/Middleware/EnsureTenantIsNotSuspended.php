<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Domain\Tenant\Exceptions\SuspendTenantException;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTenantIsNotSuspended
{
    /**
     * @throws SuspendTenantException
     */
    public function handle(Request $request, Closure $next, ?string $redirectTo = null): Response
    {

        if (tenancy()->tenant?->is_suspended) {
            throw new SuspendTenantException();
            // abort(403, 'ACCESS TO THIS PAGE IS RESTRICTED. PLEASE CONTACT ADMINISTRATOR.');
        }

        return $next($request);
    }
}
