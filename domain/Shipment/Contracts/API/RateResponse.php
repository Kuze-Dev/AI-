<?php

declare(strict_types=1);

namespace Domain\Shipment\Contracts\API;

interface RateResponse
{
    public function getRateResponseAPI(): array;

    public function getRate(int|string|null $serviceID = null): float;
}
