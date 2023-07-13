<?php

declare(strict_types=1);

namespace Domain\Shipment\Drivers;

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
