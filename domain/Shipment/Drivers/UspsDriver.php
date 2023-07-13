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
    protected string $name = 'usps';

    protected RateClient $rateClient;

    public function __construct()
    {
        $this->rateClient = app(RateClient::class);
    }

    public function getRate(array $parcelData, AddressValidateRequestData $addressValidateRequestData): float
    {

        // First or create

        $customer = Customer::with('verifiedAddress')->where('id', 1)->first();

        if ($customer->verifiedAddress) {

            $verifiedAddress = $customer->verifiedAddress;

            #check if customer shipping was Change

            if ($verifiedAddress->address != $addressValidateRequestData->toArray()) {

                $updatedVerifiedAddress = app(AddressClient::class)->verify($addressValidateRequestData);

                $verifiedAddress->update([
                    'address' => $addressValidateRequestData->toArray(),
                    'verified_address' => $updatedVerifiedAddress->toArray(),
                ]);

            }

            $zipDestination = $verifiedAddress->verified_address['zip5'];

        } else {

            $address = app(AddressClient::class)->verify($addressValidateRequestData);

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
        )->rate;
    }
}
