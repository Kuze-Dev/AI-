<?php

declare(strict_types=1);

namespace Domain\Auth\Actions;

use Domain\Auth\Contracts\TwoFactorAuthenticatable;
use Domain\Auth\Contracts\TwoFactorAuthenticationProvider;

class ValidateTotpCodeAction
{
    public function __construct(
        protected TwoFactorAuthenticationProvider $twoFactorAuthenticationProvider
    ) {}

    public function execute(TwoFactorAuthenticatable $authenticatable, string $code): bool
    {
        return $this->twoFactorAuthenticationProvider->verify(
            $authenticatable->twoFactorAuthentication->secret,
            $code
        );
    }
}
