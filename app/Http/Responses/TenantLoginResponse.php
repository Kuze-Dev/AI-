<?php

declare(strict_types=1);

namespace App\Http\Responses;

use Filament\Http\Responses\Auth\Contracts\LoginResponse as LoginResponseContract;
use Illuminate\Http\RedirectResponse;
use Livewire\Features\SupportRedirects\Redirector;

class TenantLoginResponse implements LoginResponseContract
{
    public function toResponse($request): RedirectResponse
    {
        $url = route('filament.tenant.pages.ai-widget');

        $redirector = redirect()->intended($url);

        // If it's a Livewire redirector, convert it to a real RedirectResponse
        if ($redirector instanceof Redirector) {
            return $redirector->response($url);
        }

        return $redirector;
    }
}
