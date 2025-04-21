<?php

declare(strict_types=1);

namespace Domain\Auth\Actions;

use Domain\Auth\Contracts\TwoFactorAuthenticatable;
use Illuminate\Http\Request;

class CheckIfOnSafeDeviceAction
{
    public function __construct(
        protected Request $request
    ) {}

    public function execute(TwoFactorAuthenticatable $authenticatable): bool
    {
        if (! $authenticatable->hasEnabledTwoFactorAuthentication()) {
            return false;
        }

        if (! $token = $this->request->cookie(config('domain.auth.two_factor.safe_devices.cookie'))) {
            return false;
        }

        return $authenticatable->twoFactorAuthentication->safeDevices()
            ->whereRememberToken($token)
            ->where('created_at', '<', now()->addDays(config('domain.auth.two_factor.safe_devices.expiration_days')))
            ->exists();
    }
}
