<?php

declare(strict_types=1);

namespace Domain\Shipment\Drivers;

use Domain\Shipment\API\USPS\Clients\AddressClient;
use Domain\Shipment\API\USPS\Clients\RateClient;
use Domain\Shipment\API\USPS\DataTransferObjects\AddressValidateRequestData;
use Domain\Shipment\API\USPS\DataTransferObjects\RateV4RequestData;
use Domain\Shipment\API\USPS\Enums\ServiceType;

class UspsDriver
{
    protected string $name = 'usps';

    protected RateClient $rateClient;

    public function __construct(private readonly AddressValidateRequestData $addressValidateRequestData)
    {
        $this->rateClient = app(RateClient::class);
    }

    public function getRate(): float
    {

        // First or create

        // if none then request, else reused
        $address = app(AddressClient::class)->verify($this->addressValidateRequestData);

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
