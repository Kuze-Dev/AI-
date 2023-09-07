<?php

declare(strict_types=1);

namespace Domain\Shipment\Drivers;

use Domain\Address\Models\Address;
use Domain\Customer\Models\Customer;
use Domain\Shipment\API\USPS\Contracts\RateResponse;
use Domain\Shipment\DataTransferObjects\ParcelData;
use Domain\Shipment\DataTransferObjects\StorePickupResponseData;
use Domain\ShippingMethod\Models\ShippingMethod;

class StorePickupDriver
{
    public function getRate(
        Customer $customer,
        ParcelData $parcelData,
        Address $address,
        ShippingMethod $shippingMethod
    ): RateResponse {
        return new StorePickupResponseData();
    }

    public function getInternationalRate(
        Customer $customer,
        ParcelData $parcelData,
        Address $address,
    ): RateResponse {
        return new StorePickupResponseData();
    }
}
