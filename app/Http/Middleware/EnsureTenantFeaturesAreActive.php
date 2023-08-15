<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureTenantFeaturesAreActive
{
    public function handle(Request $request, Closure $next, string ...$features): mixed
    {
        return tenancy()->tenant?->features()->someAreActive($features)
            ? $next($request)
            : abort(404);
    }
}
