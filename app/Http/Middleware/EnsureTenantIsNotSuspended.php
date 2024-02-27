<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Domain\Tenant\Exceptions\SuspendTenantException;
use Domain\Tenant\TenantSupport;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTenantIsNotSuspended
{
    /**
     * @throws SuspendTenantException
     */
    public function handle(Request $request, Closure $next, ?string $redirectTo = null): Response
    {
        if (TenantSupport::model()->is_suspended) {
            throw new SuspendTenantException();
        }

        return $next($request);
    }
}
