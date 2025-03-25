<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RestrictApiAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($this->isAllowedOrigin($request) || $this->hasValidRateKey($request)) {
            return $next($request);
        }

        if ($request->is('api/*')) {
            return response()->json(['message' => 'Access denied'], 403);
        }

        return $next($request);
    }

    private function isAllowedOrigin(Request $request): bool
    {
        $origin = $request->header('Origin');
        $allowedOrigins = config('cors.allowed_origins');

        if (in_array('*', $allowedOrigins, true)) {
            return true;
        }

        foreach ($allowedOrigins as $allowedOrigin) {
            if ($origin === $allowedOrigin || $this->matchesWildcardOrigin($origin, $allowedOrigin)) {
                return true;
            }
        }

        return false;
    }

    private function matchesWildcardOrigin(?string $origin, string $allowedOrigin): bool
    {
        if (str_starts_with($allowedOrigin, '*.') && $origin) {
            $allowedBaseDomain = substr($allowedOrigin, 2);

            return str_ends_with($origin, ".$allowedBaseDomain");
        }

        return false;
    }

    private function hasValidRateKey(Request $request): bool
    {
        if ($request->hasHeader('x-rate-key')) {
            $rateKey = $request->header('x-rate-key');

            return $rateKey === config('custom.rate_limit_key');
        }

        return false;
    }
}
