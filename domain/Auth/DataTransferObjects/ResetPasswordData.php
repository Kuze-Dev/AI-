<?php

namespace Domain\Auth\DataTransferObjects;

class ResetPasswordData
{
    public function __construct(
        public readonly string $email,
        public readonly string $password,
        public readonly string $token,
    ) {
    }
}
