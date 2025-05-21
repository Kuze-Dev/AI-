<?php

declare(strict_types=1);

namespace Domain\Auth\Events;

use Domain\Auth\Contracts\HasActiveState;

class Activated
{
    public function __construct(
        public HasActiveState $user
    ) {}
}
