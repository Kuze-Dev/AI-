<?php

declare(strict_types=1);

namespace Domain\Shipment\API\USPS\DataTransferObjects;

class RateInternationalV2ResponseData
{
    public function __construct(
        public readonly float $rate
    ) {
    }
}
