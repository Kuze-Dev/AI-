<?php

declare(strict_types=1);

namespace Domain\Shipment\Drivers;

use Domain\Customer\Models\Customer;
use Domain\Shipment\Actions\USPS\GetUSPSInternationalRateDataAction;
use Domain\Shipment\Actions\USPS\GetUSPSRateDataAction;
use Domain\Shipment\Contracts\API\RateResponse;
use Domain\Shipment\API\USPS\DataTransferObjects\AddressValidateRequestData;
use Domain\Shipment\DataTransferObjects\ParcelData;
use Domain\Shipment\DataTransferObjects\ShippingAddressData;

class UspsDriver
{
    public function getRate(
        Customer $customer,
        ParcelData $parcelData,
        ShippingAddressData $address// AddressValidateRequestData $addressValidateRequestData
    ): RateResponse {
        return app(GetUSPSRateDataAction::class)->execute(
            $customer,
            $parcelData,
            AddressValidateRequestData::fromShippingDataDTO($address),
        );
    }

    public function getInternationalRate(
        Customer $customer,
        ParcelData $parcelData,
        ShippingAddressData $address,
    ): RateResponse {
        return app(GetUSPSInternationalRateDataAction::class)->execute(
            $customer,
            $parcelData,
            $address,
        );
    }
}
