<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Domain\Tenant\TenantFeatureSupport;
use Illuminate\Http\Request;
use Laravel\Pennant\Feature;

class EnsureTenantFeaturesAreActive
{
    public function handle(Request $request, Closure $next, string ...$features): mixed
    {
//        Feature::loadMissing($features);

        /** @phpstan-ignore argument.type */
        if(TenantFeatureSupport::someAreActive($features)) {
            return $next($request);
        }

        abort(404, 'Some features are not active in this tenant');
    }
}
