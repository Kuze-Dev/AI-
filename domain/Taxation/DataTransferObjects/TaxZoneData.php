<?php

declare(strict_types=1);

namespace Domain\Taxation\DataTransferObjects;

use Domain\Taxation\Enums\PriceDisplay;
use Domain\Taxation\Enums\TaxZoneType;

class TaxZoneData
{
    public function __construct(
        public readonly string $name,
        public readonly PriceDisplay $price_display,
        public readonly bool $is_active,
        public readonly bool $is_default,
        public readonly TaxZoneType $type,
        public readonly float $percentage,
        public readonly array $countries = [],
        public readonly array $states = [],
    ) {}

    public static function formArray(array $data): self
    {
        return new self(
            name: $data['name'],
            price_display: ! $data['price_display'] instanceof PriceDisplay
                ? PriceDisplay::from($data['price_display'])
                : $data['price_display'],
            is_active: $data['is_active'],
            is_default: $data['is_default'],
            type: ! $data['type'] instanceof TaxZoneType
                ? TaxZoneType::from($data['type'])
                : $data['type'],
            percentage: $data['percentage'],
            countries: $data['countries'] ?? [],
            states: $data['states'] ?? [],
        );
    }
}
