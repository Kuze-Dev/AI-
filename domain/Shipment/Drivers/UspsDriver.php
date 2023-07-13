<?php

declare(strict_types=1);

namespace Domain\Shipment\Drivers;

use Domain\Shipment\API\USPS\Enums\ServiceType;
use Domain\Shipment\API\USPS\RateClient;
use Domain\Shipment\DataTransferObjects\RateV4RequestData;

class UspsDriver
{
    protected string $name = 'usps';

    protected RateClient $rateClient;

    public function __construct()
    {
        $this->rateClient = app(RateClient::class);
    }

    public function withAddress(): self
    {

        return $this;
    }

    public function getRate(): float
    {
        return $this->rateClient->getV4(
            new RateV4RequestData(
                Service: ServiceType::PRIORITY,
                ZipOrigination:'94107',
                ZipDestination:'26301',
                Pounds:'8',
                Ounces:'2',
            )
        )->rate;
    }
}
