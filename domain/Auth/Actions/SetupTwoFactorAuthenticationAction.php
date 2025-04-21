<?php

declare(strict_types=1);

namespace Domain\Auth\Actions;

use Domain\Auth\Contracts\TwoFactorAuthenticatable;
use Domain\Auth\Contracts\TwoFactorAuthenticationProvider;

class SetupTwoFactorAuthenticationAction
{
    public function __construct(
        protected TwoFactorAuthenticationProvider $twoFactorAuthenticationProvider
    ) {}

    public function execute(TwoFactorAuthenticatable $authenticatable): TwoFactorAuthenticatable
    {
        $authenticatable->twoFactorAuthentication()
            ->firstOrNew()
            ->forceFill(['secret' => $this->twoFactorAuthenticationProvider->generateSecretKey()])
            ->save();

        return $authenticatable;
    }
}
