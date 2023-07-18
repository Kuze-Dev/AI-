<?php

declare(strict_types=1);

namespace Domain\Shipment\DataTransferObjects;

class ShippingRateActionReturn
{
    public function __construct(
        public readonly float $rate,
        public readonly bool $isUnitedStateDomestic,
    ) {
    }
}
