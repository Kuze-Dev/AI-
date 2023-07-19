<?php

declare(strict_types=1);

namespace Domain\Shipment\API\USPS\DataTransferObjects\RateV4Response;

use Domain\Shipment\API\USPS\Contracts\RateResponse;

class RateV4ResponseData implements RateResponse
{
    public function __construct(
        public readonly float $rate
    ) {
    }

    public function getRateResponseAPI(): array
    {
        return get_object_vars($this);
    }
}
