<?php

declare(strict_types=1);

namespace Domain\Auth\Events;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

readonly class PasswordResetSent
{
    public function __construct(
        public Authenticatable&Model $user
    ) {}
}
