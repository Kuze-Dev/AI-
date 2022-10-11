<?php

declare(strict_types=1);

namespace Domain\Auth\DataTransferObjects;

class TwoFactorData
{
    public function __construct(
        public readonly ?string $code = null,
        public readonly ?string $recovery_code = null,
        public readonly bool $remember_device = false,
        public readonly ?string $guard = null,
    ) {
    }
}
