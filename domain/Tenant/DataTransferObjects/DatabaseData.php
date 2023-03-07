<?php

declare(strict_types=1);

namespace Domain\Tenant\DataTransferObjects;

class DatabaseData
{
    public function __construct(
        public readonly string $host,
        public readonly string $port,
        public readonly string $name,
        public readonly string $username,
        public readonly string $password,
    ) {
    }
}
