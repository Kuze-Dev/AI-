<?php

declare(strict_types=1);

namespace Domain\Auth\DataTransferObjects;

readonly class SafeDeviceData
{
    public function __construct(
        public string $ip,
        public string $userAgent,
    ) {
    }
}
