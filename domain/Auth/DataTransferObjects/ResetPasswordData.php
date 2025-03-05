<?php

declare(strict_types=1);

namespace Domain\Auth\DataTransferObjects;

readonly class ResetPasswordData
{
    public function __construct(
        public string $email,
        public string $password,
        public string $token,
    ) {
    }
}
