<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Filament\Facades\Filament;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureTenantIsNotSuspended
{
    public function handle(Request $request, Closure $next, string $redirectTo = null): Response
    {
       
        #check if tenant is suspended

        if( tenancy()->tenant?->is_suspended){

            abort(403, 'Your account has been suspended. Please contact the administrator.');
        }

        return $next($request);
    }
}
