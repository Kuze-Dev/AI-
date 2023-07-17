<?php

declare(strict_types=1);

namespace Domain\Cart\DataTransferObjects;

use Domain\Taxation\Enums\PriceDisplay;
use Domain\Taxation\Models\TaxZone;

class SummaryData
{
    public function __construct(
        public readonly float $subTotal,
        public readonly TaxZone $taxZone,
        public readonly PriceDisplay $taxDisplay,
        public readonly float $taxPercentage,
        public readonly float $taxTotal,
        public readonly float $grandTotal,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            subTotal: $data['subTotal'],
            taxZone: $data['taxZone'],
            taxDisplay: $data['taxDisplay'],
            taxPercentage: $data['taxPercentage'] ?? null,
            taxTotal: $data['taxTotal'] ?? null,
            grandTotal: $data['grandTotal'] ?? null,
        );
    }
}
