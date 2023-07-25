<?php

declare(strict_types=1);

namespace Domain\Shipment\Drivers;

use Domain\Address\Models\Address;
use Domain\Customer\Models\Customer;
use Domain\Shipment\Actions\UPS\GetUPSRateDataAction;
use Domain\Shipment\Actions\USPS\GetUSPSInternationalRateDataAction;
use Domain\Shipment\API\USPS\Contracts\RateResponse;
use Domain\Shipment\API\USPS\DataTransferObjects\AddressValidateRequestData;
use Domain\Shipment\DataTransferObjects\ParcelData;
use Domain\ShippingMethod\Models\ShippingMethod;

class UpsDriver
{
    public function getRate(
        Customer $customer,
        ParcelData $parcelData,
        AddressValidateRequestData $addressValidateRequestData,
        ShippingMethod $shippingMethod
    ): RateResponse {

        return app(GetUPSRateDataAction::class)->execute(
            $customer,
            $parcelData,
            $addressValidateRequestData,
            $shippingMethod
        );
    }

    public function getInternationalRate(
        Customer $customer,
        ParcelData $parcelData,
        Address $address,
    ): RateResponse {
        return app(GetUSPSInternationalRateDataAction::class)->execute(
            $customer,
            $parcelData,
            $address,
        );
    }
}
