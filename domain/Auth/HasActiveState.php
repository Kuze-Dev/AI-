<?php

declare(strict_types=1);

namespace Domain\Auth;

use Domain\Auth\Notifications\ActivateAccount;

trait HasActiveState
{
    public function isActive(): bool
    {
        return (bool) $this->active;
    }

    public function sendActivateAccountNotification(): void
    {
        $this->notify(new ActivateAccount);
    }
}
