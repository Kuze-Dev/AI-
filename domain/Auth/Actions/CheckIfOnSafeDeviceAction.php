<?php

namespace Domain\Auth\Actions;

use Domain\Auth\Contracts\TwoFactorAuthenticatable;
use Illuminate\Http\Request;

class CheckIfOnSafeDeviceAction
{
    public function execute(TwoFactorAuthenticatable $authenticatable, Request $request): bool
    {
        if (! $authenticatable->hasEnabledTwoFactorAuthentication()) {
            return false;
        }

        if (! $token = $request->cookie(config('domain.auth.two_factor.safe_devices.cookie'))) {
            return false;
        }

        return $authenticatable->twoFactorAuthentication->safeDevices()
            ->whereRememberToken($token)
            ->where('created_at', '<', now()->addDays(config('domain.auth.two_factor.safe_devices.expiration_days')))
            ->exists();
    }
}
