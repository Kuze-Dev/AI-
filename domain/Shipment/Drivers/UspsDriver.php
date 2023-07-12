<?php

declare(strict_types=1);

namespace Domain\Shipment\Drivers;

use Domain\Shipment\API\USPS\RateClient;

class UspsDriver
{
    protected string $name = 'usps';

    public function __construct(private readonly RateClient $rateClient)
    {

    }

    public function handle(): void
    {

    }

    public function getRate(): float
    {
        return $this->rateClient->getV4()->rate;
    }
}
