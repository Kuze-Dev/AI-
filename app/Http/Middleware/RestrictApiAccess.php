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
        $origin = $request->header('Origin');

        $allowed_origins = config('cors.allowed_origins');

        if (in_array('*', $allowed_origins) ||
            (
                $request->hasHeader('x-rate-key') &&
                $request->header('x-rate-key') === config('custom.rate_limit_key')
            )
        ) {
            return $next($request);
        } elseif (
            $request->header('x-rate-key') !== config('custom.rate_limit_key')
        ) {
            return response()->json(['message' => 'Invalid Key Credential'], 403);
        }

        // If the request is an API request but the origin is not allowed, deny access
        if ($request->is('api/*') && (! in_array($origin, config('cors.allowed_origins')))) {
            return response()->json(['message' => 'Access denied from .'], 403);
        }

        return $next($request);
    }
}
