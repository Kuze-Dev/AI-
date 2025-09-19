<?php

declare(strict_types=1);

namespace App\Http\Responses;

use App\Features\AI\UploadBase;
use Illuminate\Http\RedirectResponse;
use Domain\Tenant\TenantFeatureSupport;
use Livewire\Features\SupportRedirects\Redirector;
use Filament\Http\Responses\Auth\Contracts\LoginResponse as LoginResponseContract;

class TenantLoginResponse implements LoginResponseContract
{
    public function toResponse($request): RedirectResponse
    {
        // Default redirect
        $url = route('filament.tenant.pages.dashboard');

        // Only check tenant features if tenancy is initialized
        if (tenancy()->initialized && TenantFeatureSupport::active(UploadBase::class)) {
            $url = route('filament.tenant.pages.ai-widget');
        }

        $redirector = redirect()->intended($url);

        if ($redirector instanceof Redirector) {
            return $redirector->response($url);
        }

        return $redirector;
    }
}
