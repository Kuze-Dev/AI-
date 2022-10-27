<?php

declare(strict_types=1);

namespace App\FilamentTenant\Livewire\Auth;

use App\Filament\Livewire\Auth\Login as BaseLogin;
use Illuminate\Http\RedirectResponse;
use Livewire\Redirector;

class Login extends BaseLogin
{
    public function redirectToTwoFactorAuthentication(): Redirector|RedirectResponse
    {
        return redirect()->route('filament-tenant.auth.two-factor');
    }

    public function redirectToRequestPasswordReset(): Redirector|RedirectResponse
    {
        return redirect()->route('filament-tenant.auth.password.request');
    }
}
