<?php

declare(strict_types=1);

namespace Domain\Auth\Events;

use Domain\Auth\Contracts\TwoFactorAuthenticatable;

class SafeDevicesFlushed
{
    public function __construct(
        public TwoFactorAuthenticatable $user
    ) {}
}
