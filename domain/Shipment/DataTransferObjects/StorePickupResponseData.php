<?php

declare(strict_types=1);

namespace Domain\Shipment\DataTransferObjects;

use Domain\Shipment\Contracts\API\RateResponse;

class StorePickupResponseData implements RateResponse
{
    public function __construct(
        public readonly float $rate = 0.00
    ) {}

    #[\Override]
    public function getRateResponseAPI(): array
    {
        return get_object_vars($this);
    }

    #[\Override]
    public function getRate(int|string|null $serviceID = null): float
    {
        return $this->rate;
    }
}
