<?php

declare(strict_types=1);

namespace Domain\Auth\DataTransferObjects;

class SafeDeviceData
{
    public function __construct(
        public readonly string $ip,
        public readonly string $userAgent,
    ) {
    }
}
