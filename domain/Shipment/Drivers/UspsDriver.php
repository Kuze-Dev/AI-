<?php

declare(strict_types=1);

namespace Domain\Shipment\Drivers;

use Domain\Shipment\Actions\USPS\GetUSPSInternationalRateDataAction;
use Domain\Shipment\Actions\USPS\GetUSPSRateDataAction;
use Domain\Shipment\API\USPS\DataTransferObjects\AddressValidateRequestData;
use Domain\Shipment\Contracts\API\RateResponse;
use Domain\Shipment\DataTransferObjects\ParcelData;
use Domain\Shipment\DataTransferObjects\ShippingAddressData;

class UspsDriver
{
    public function getRate(
        ParcelData $parcelData,
        ShippingAddressData $address// AddressValidateRequestData $addressValidateRequestData
    ): RateResponse {
        return app(GetUSPSRateDataAction::class)->execute(
            $parcelData,
            AddressValidateRequestData::fromShippingDataDTO($address),
        );
    }

    public function getInternationalRate(
        ParcelData $parcelData,
        ShippingAddressData $address,
    ): RateResponse {
        return app(GetUSPSInternationalRateDataAction::class)->execute(
            $parcelData,
            $address,
        );
    }
}
