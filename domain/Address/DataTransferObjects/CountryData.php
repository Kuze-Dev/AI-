<?php

declare(strict_types=1);

namespace Domain\Address\DataTransferObjects;

use Domain\Address\Enums\CountryStateOrRegion;

class CountryData
{
    public function __construct(
        public readonly string $code,
        public readonly string $name,
        public readonly string $capital,
        public readonly CountryStateOrRegion $state_or_region,
        public readonly string $timezone,
        public readonly string $language,
        public readonly bool $active,
    ) {
    }
}
