<?php

declare(strict_types=1);

namespace Domain\Shipment\API\USPS\DataTransferObjects;

class ShippingRateData
{
    public function __construct(
        public readonly float $rate,
    ) {
    }
}
