<?php

declare(strict_types=1);

namespace Domain\Shipment\Drivers;

use Domain\Customer\Models\Customer;
use Domain\Shipment\Actions\UPS\GetUPSInternationalRateDataAction;
use Domain\Shipment\Actions\UPS\GetUPSRateDataAction;
use Domain\Shipment\API\USPS\Contracts\RateResponse;
use Domain\Shipment\DataTransferObjects\ParcelData;
use Domain\Shipment\DataTransferObjects\ShippingAddressData;
use Domain\ShippingMethod\Models\ShippingMethod;

class UpsDriver
{
    public function getRate(
        Customer $customer,
        ParcelData $parcelData,
        ShippingAddressData $address,
        ShippingMethod $shippingMethod
    ): RateResponse {

        return app(GetUPSRateDataAction::class)->execute(
            $customer,
            $parcelData,
            $address,
            $shippingMethod
        );
    }

    public function getInternationalRate(
        Customer $customer,
        ParcelData $parcelData,
        ShippingAddressData $address,
    ): RateResponse {
        return app(GetUPSInternationalRateDataAction::class)->execute(
            $customer,
            $parcelData,
            $address,
        );
    }
}
