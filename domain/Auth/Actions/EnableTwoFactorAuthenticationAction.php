<?php

declare(strict_types=1);

namespace Domain\Auth\Actions;

use Domain\Auth\Contracts\TwoFactorAuthenticatable;
use Domain\Auth\Events\TwoFactorAuthenticationEnabled;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\Event;

class EnableTwoFactorAuthenticationAction
{
    public function __construct(
        protected ValidateTotpCodeAction $validator
    ) {}

    public function execute(User&TwoFactorAuthenticatable $authenticatable, string $code): ?bool
    {
        if ($authenticatable->hasEnabledTwoFactorAuthentication()) {
            return null;
        }

        if (! $this->validator->execute($authenticatable, $code)) {
            return false;
        }

        $authenticatable->twoFactorAuthentication()
            ->firstOrFail()
            ->forceFill(['enabled_at' => now()])
            ->save();

        if ($authenticatable->relationLoaded('twoFactorAuthentication')) {
            $authenticatable->load('twoFactorAuthentication');
        }

        Event::dispatch(new TwoFactorAuthenticationEnabled($authenticatable));

        return true;
    }
}
