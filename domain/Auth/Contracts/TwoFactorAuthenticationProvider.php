<?php

namespace Domain\Auth\Contracts;

interface TwoFactorAuthenticationProvider
{
    public function generateSecretKey(): string;

    public function qrCodeUrl(string $name, string $holder, string $secret): string;

    public function verify(string $secret, string $code): bool;
}
