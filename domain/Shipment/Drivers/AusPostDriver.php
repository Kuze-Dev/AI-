<?php

declare(strict_types=1);

namespace Domain\Shipment\Drivers;

use Domain\Customer\Models\Customer;
use Domain\Shipment\Actions\AusPost\GetAuspostRateDataAction;
use Domain\Shipment\Contracts\API\RateResponse;
use Domain\Shipment\DataTransferObjects\ParcelData;
use Domain\Shipment\DataTransferObjects\ShippingAddressData;
use Domain\Shipment\DataTransferObjects\StorePickupResponseData;
use Domain\ShippingMethod\Models\ShippingMethod;

class AusPostDriver
{
    public function getRate(
        Customer $customer,
        ParcelData $parcelData,
        ShippingAddressData $address,
        ShippingMethod $shippingMethod
    ): RateResponse {

        return app(GetAuspostRateDataAction::class)->execute(
            $customer,
            $parcelData,
            $address
        );
    }

    public function getInternationalRate(
        Customer $customer,
        ParcelData $parcelData,
        ShippingAddressData $address,
        ShippingMethod $shippingMethod
    ): RateResponse {
        return new StorePickupResponseData();
    }
}
