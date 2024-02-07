<?php

declare(strict_types=1);

namespace Domain\Shipment\API\AusPost\Contracts;

interface RateResponse
{
    public function getRateResponseAPI(): array;

    public function getRate(int|string|null $serviceID = null): string|int|float;
}
