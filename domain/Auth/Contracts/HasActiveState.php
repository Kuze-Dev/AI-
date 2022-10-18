<?php

declare(strict_types=1);

namespace Domain\Auth\Contracts;

interface HasActiveState
{
    public function isActive(): bool;

    public function sendActivateAccountNotification(): void;
}
