<?php

declare(strict_types=1);

namespace App\FilamentTenant\Middleware;

use Filament\Http\Middleware\Authenticate as BaseAuthenticate;

class Authenticate extends BaseAuthenticate
{
    protected function redirectTo($request): string
    {
        return route('filament.tenant.auth.login');
    }
}
