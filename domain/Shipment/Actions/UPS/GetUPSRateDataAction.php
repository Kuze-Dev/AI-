<?php

declare(strict_types=1);

namespace Domain\Shipment\Actions\UPS;

use Domain\Customer\Models\Customer;
use Domain\Shipment\API\USPS\Clients\AddressClient;
use Domain\Shipment\API\UPS\Clients\UPSRateClient;
use Domain\Shipment\API\USPS\DataTransferObjects\AddressValidateRequestData;
use Domain\Shipment\DataTransferObjects\ParcelData;

class GetUPSRateDataAction
{
    public function __construct(
        private readonly UPSRateClient $rateClient,
        private readonly AddressClient $addressClient
    ) {
    }

    public function execute(
        Customer $customer,
        ParcelData $parcelData,
        AddressValidateRequestData $addressValidateRequestData,
    ) {

        $verifiedAddress = $customer->verifiedAddress;

        if ($verifiedAddress !== null) {

            if ($verifiedAddress->address != $addressValidateRequestData->toArray()) {

                $updatedVerifiedAddress = $this->addressClient->verify($addressValidateRequestData);

                $verifiedAddress->update([
                    'address' => $addressValidateRequestData->toArray(),
                    'verified_address' => $updatedVerifiedAddress->toArray(),
                ]);

            }

        } else {

            $address = $this->addressClient->verify($addressValidateRequestData);

            $customer->verifiedAddress()->create([
                'address' => $addressValidateRequestData->toArray(),
                'verified_address' => $address->toArray(),
            ]);

        }

        return $this->rateClient->getRate(
            customer: $customer,
            parcelData: $parcelData,
            verifiedAddress: $customer->verifiedAddress,
        );
    }
}
