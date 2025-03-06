<?php

declare(strict_types=1);

namespace Domain\Address\DataTransferObjects;

readonly class CountryData
{
    public function __construct(
        public string $code,
        public string $name,
        public string $capital,
        public string $timezone,
        public bool $active,
    ) {}
}
