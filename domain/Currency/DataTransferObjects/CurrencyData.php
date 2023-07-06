<?php

declare(strict_types=1);

namespace Domain\Currency\DataTransferObjects;

class CurrencyData
{
    public function __construct(
        public readonly string $code,
        public readonly string $name,
        public readonly bool $enabled,
        public readonly float $exchange_rate,
        public readonly bool $default,
    ) {
    }
}
