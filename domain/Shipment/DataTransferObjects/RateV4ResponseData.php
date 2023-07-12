<?php

declare(strict_types=1);

namespace Domain\Shipment\DataTransferObjects;

class RateV4ResponseData
{
    public function __construct(
        public readonly float $rate
    ) {
    }
}
