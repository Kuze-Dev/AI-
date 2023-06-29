<?php

declare(strict_types=1);

namespace Domain\Address\DataTransferObjects;

class CountryData
{
    public function __construct(
        public readonly string $code,
        public readonly string $name,
        public readonly string $capital,
        public readonly string $timezone,
        public readonly string $language,
        public readonly bool $active,
    ) {
    }
}
