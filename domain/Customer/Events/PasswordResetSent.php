<?php

declare(strict_types=1);

namespace Domain\Customer\Events;

use Illuminate\Contracts\Auth\Authenticatable;

class PasswordResetSent
{
    public function __construct(
        public readonly Authenticatable $user
    ) {
    }
}
