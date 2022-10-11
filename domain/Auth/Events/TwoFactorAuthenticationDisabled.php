<?php

namespace Domain\Auth\Events;

use Domain\Auth\Contracts\TwoFactorAuthenticatable;

class TwoFactorAuthenticationDisabled
{
    public function __construct(
        public TwoFactorAuthenticatable $user
    ) {
    }
}
