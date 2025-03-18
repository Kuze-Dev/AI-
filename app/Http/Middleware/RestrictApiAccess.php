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

        /** @var array */
        $allowed_origins = config('cors.allowed_origins');

        if (in_array('*', $allowed_origins, true)) {
            return $next($request);
        }

        // If the request is an API request but the origin is not allowed, deny access
        if ($request->is('api/*') && (! in_array($origin, $allowed_origins, true))) {
            return response()->json(['message' => 'Access denied.'], 403);
        }

        return $next($request);
    }
}
