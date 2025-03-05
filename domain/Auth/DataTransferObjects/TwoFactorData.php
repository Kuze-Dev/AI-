<?php

declare(strict_types=1);

namespace Domain\Auth\DataTransferObjects;

readonly class TwoFactorData
{
    public function __construct(
        public ?string $code = null,
        public ?string $recovery_code = null,
        public bool $remember_device = false,
        public ?string $guard = null,
    ) {
    }
}
