<?php

declare(strict_types=1);

namespace Domain\Shipment\API\USPS\Contracts;

interface RateResponse
{
    public function getRateResponseAPI(): array;

    public function getRate(int|string $serviceID = null): float;
}
