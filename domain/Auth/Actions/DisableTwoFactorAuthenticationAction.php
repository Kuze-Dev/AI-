<?php

declare(strict_types=1);

namespace Domain\Auth\Actions;

use Domain\Auth\Contracts\TwoFactorAuthenticatable;
use Domain\Auth\Contracts\TwoFactorAuthenticationProvider;
use Domain\Auth\Events\TwoFactorAuthenticationDisabled;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\Event;

class DisableTwoFactorAuthenticationAction
{
    public function execute(User&TwoFactorAuthenticatable $authenticatable): ?bool
    {
        if ( ! $authenticatable->hasEnabledTwoFactorAuthentication()) {
            return null;
        }

        $authenticatable->twoFactorAuthentication()
            ->firstOrNew()
            ->forceFill([
                'enabled_at' => null,
                'secret' => app(TwoFactorAuthenticationProvider::class)->generateSecretKey(),
            ])
            ->save();

        if ($authenticatable->relationLoaded('twoFactorAuthentication')) {
            $authenticatable->load('twoFactorAuthentication');
        }

        Event::dispatch(new TwoFactorAuthenticationDisabled($authenticatable));

        return true;
    }
}
