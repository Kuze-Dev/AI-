<?php

declare(strict_types=1);

namespace Domain\Shipment\Actions\USPS;

use Domain\Shipment\API\USPS\Clients\AddressClient;
use Domain\Shipment\API\USPS\Clients\RateClient;
use Domain\Shipment\API\USPS\DataTransferObjects\AddressValidateRequestData;
use Domain\Shipment\API\USPS\DataTransferObjects\RateV4RequestData;
use Domain\Shipment\API\USPS\DataTransferObjects\RateV4Response\RateV4ResponseData;
use Domain\Shipment\API\USPS\Enums\ServiceType;
use Domain\Shipment\DataTransferObjects\ParcelData;
use Illuminate\Support\Facades\Auth;

class GetUSPSRateDataAction
{
    public function __construct(
        private readonly RateClient $rateClient,
        private readonly AddressClient $addressClient
    ) {}

    public function execute(
        ParcelData $parcelData,
        AddressValidateRequestData $addressValidateRequestData
    ): RateV4ResponseData {

        /** @var \Domain\Customer\Models\Customer */
        $customer = Auth::user();

        if ($customer != null) {

            $verifiedAddress = $customer->verifiedAddress;

            if ($verifiedAddress !== null) {

                if ($verifiedAddress->address != $addressValidateRequestData->toArray()) {

                    $updatedVerifiedAddress = $this->addressClient->verify($addressValidateRequestData);

                    $verifiedAddress->update([
                        'address' => $addressValidateRequestData->toArray(),
                        'verified_address' => $updatedVerifiedAddress->toArray(),
                    ]);

                }

                $zipDestination = $verifiedAddress->verified_address['zip5'] ?? null;

            } else {

                $address = $this->addressClient->verify($addressValidateRequestData);

                $customer->verifiedAddress()->create([
                    'address' => $addressValidateRequestData->toArray(),
                    'verified_address' => $address->toArray(),
                ]);

                $zipDestination = $address->zip5;
            }
        } else {

            $address = $this->addressClient->verify($addressValidateRequestData);

            $zipDestination = $address->zip5;
        }

        return $this->rateClient->getV4(
            new RateV4RequestData(
                Service: ServiceType::PRIORITY,
                ZipOrigination: $parcelData->zip_origin,
                ZipDestination: $zipDestination,
                Pounds: $parcelData->pounds,
                Ounces: $parcelData->ounces,
            )
        );
    }
}
