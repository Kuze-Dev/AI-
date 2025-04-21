<?php

declare(strict_types=1);

namespace Domain\ServiceOrder\DataTransferObjects;

use Domain\Taxation\Enums\PriceDisplay;

class ServiceOrderTaxData
{
    public function __construct(
        public readonly int|float $sub_total,
        public readonly PriceDisplay|null|string $tax_display,
        public readonly int|float $tax_percentage,
        public readonly int|float $tax_total,
        public readonly int|float $total_price
    ) {}
}
