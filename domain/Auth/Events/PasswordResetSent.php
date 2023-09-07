<?php

declare(strict_types=1);

namespace Domain\Auth\Events;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class PasswordResetSent
{
    public function __construct(
        public readonly Authenticatable&Model $user
    ) {
    }
}
