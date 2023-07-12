<?php

declare(strict_types=1);

namespace Domain\Shipment\DataTransferObjects;

class RateResponseData
{
    public function __construct(
        public readonly float $rate
    ) {
    }
}
