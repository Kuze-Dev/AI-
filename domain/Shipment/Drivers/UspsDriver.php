<?php

declare(strict_types=1);

namespace Domain\Shipment\Drivers;

use Domain\Customer\Models\Customer;
use Domain\Shipment\API\USPS\Clients\AddressClient;
use Domain\Shipment\API\USPS\Clients\RateClient;
use Domain\Shipment\API\USPS\DataTransferObjects\AddressValidateRequestData;
use Domain\Shipment\API\USPS\DataTransferObjects\RateV4RequestData;
use Domain\Shipment\API\USPS\Enums\ServiceType;

class UspsDriver
{
    //    protected string $name = 'usps';

    public function __construct(
        private readonly RateClient $rateClient,
        private readonly AddressClient $addressClient
    ) {
    }

    public function getRate(array $parcelData, AddressValidateRequestData $addressValidateRequestData): float
    {
        $customer = Customer::with('verifiedAddress')
            ->where('id', 1)
            ->first();

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
                ZipOrigination:'94107',
                ZipDestination:$zipDestination,
                Pounds: $parcelData['pounds'],
                Ounces:$parcelData['ounces'],
            )
        )
            ->rate;
    }

    public function getInternationalRate(): float
    {
        return $this->rateClient->getInternationalVersion2()->rate;
    }
}
