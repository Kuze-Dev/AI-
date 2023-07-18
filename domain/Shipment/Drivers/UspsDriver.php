<?php

declare(strict_types=1);

namespace Domain\Shipment\Drivers;

use Domain\Customer\Models\Customer;
use Domain\Shipment\API\USPS\Clients\AddressClient;
use Domain\Shipment\API\USPS\Clients\RateClient;
use Domain\Shipment\API\USPS\Contracts\RateResponse;
use Domain\Shipment\API\USPS\DataTransferObjects\AddressValidateRequestData;
use Domain\Shipment\API\USPS\DataTransferObjects\RateV4RequestData;
use Domain\Shipment\API\USPS\Enums\ServiceType;
use Domain\Shipment\DataTransferObjects\ParcelData;

class UspsDriver
{
    //    protected string $name = 'usps';

    public function __construct(
        private readonly RateClient $rateClient,
        private readonly AddressClient $addressClient
    ) {
    }

    public function getRate(
        Customer $customer,
        ParcelData $parcelData,
        AddressValidateRequestData $addressValidateRequestData
    ): RateResponse {

        if ($customer->verifiedAddress) {

            $verifiedAddress = $customer->verifiedAddress;

            # check if customer shipping was Change

            if ($verifiedAddress->address != $addressValidateRequestData->toArray()) {

                $updatedVerifiedAddress = app(AddressClient::class)->verify($addressValidateRequestData);

                $verifiedAddress->update([
                    'address' => $addressValidateRequestData->toArray(),
                    'verified_address' => $updatedVerifiedAddress->toArray(),
                ]);

            }

            $zipDestination = $verifiedAddress->verified_address['zip5'];

        } else {

            $address = $this->addressClient->verify($addressValidateRequestData);

            $customer->verifiedAddress()->create([
                'address' => $addressValidateRequestData->toArray(),
                'verified_address' => $address->toArray(),
            ]);

            $zipDestination = $address->zip5;
        }

        return $this->rateClient->getV4(
            new RateV4RequestData(
                Service: ServiceType::PRIORITY,
                ZipOrigination: '94107', // TODO: add real data here
                ZipDestination:$zipDestination,
                Pounds: $parcelData->pounds,
                Ounces:$parcelData->ounces,
            )
        );
    }

    public function getInternationalRate(): RateResponse
    {
        return $this->rateClient->getInternationalVersion2();
    }
}
