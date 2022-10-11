<?php

declare(strict_types=1);

namespace Domain\Auth\Actions;

use Domain\Auth\Contracts\TwoFactorAuthenticatable;
use Domain\Auth\Events\SafeDeviceAdded;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;

class AddSafeDeviceAction
{
    public function execute(TwoFactorAuthenticatable $authenticatable, Request $request): TwoFactorAuthenticatable
    {
        if ( ! $authenticatable->hasEnabledTwoFactorAuthentication()) {
            return $authenticatable;
        }

        $token = Str::random(60);

        $authenticatable->twoFactorAuthentication->safeDevices()
            ->make([
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ])
            ->forceFill(['remember_token' => $token])
            ->save();

        if ($authenticatable->twoFactorAuthentication->safeDevices()->count() > config('domain.auth.two_factor.safe_devices.max_devices')) {
            $authenticatable->twoFactorAuthentication->safeDevices()
                ->orderByDesc('created_at')
                ->offset(config('domain.auth.two_factor.safe_devices.max_devices'))
                ->limit(PHP_INT_MAX)
                ->delete();
        }

        Cookie::queue(
            config('domain.auth.two_factor.safe_devices.cookie'),
            $token,
            config('domain.auth.two_factor.safe_devices.expiration_days') * 1440
        );

        Event::dispatch(new SafeDeviceAdded($authenticatable, $request));

        return $authenticatable;
    }
}
