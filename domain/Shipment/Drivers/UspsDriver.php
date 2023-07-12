<?php

declare(strict_types=1);

namespace Domain\Shipment\Drivers;

use Domain\Shipment\API\USPS\RateClient;
use Domain\Shipment\DataTransferObjects\RateV4RequestData;

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
        return $this->rateClient->getV4(
            RateV4RequestData::fromArray([
                'service' => 'PRIORITY',
                'zipOrigination' => '94107',
                'zipDestination' => '26301',
                'pounds' => '8',
                'ounces' => '2',
                'container' => '',
                'machinable' => true,
            ])
        )->rate;
    }
}
