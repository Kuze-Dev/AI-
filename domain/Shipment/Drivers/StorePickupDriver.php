<?php

declare(strict_types=1);

namespace Domain\Shipment\Drivers;

use Domain\Shipment\Contracts\API\RateResponse;
use Domain\Shipment\DataTransferObjects\ParcelData;
use Domain\Shipment\DataTransferObjects\ShippingAddressData;
use Domain\Shipment\DataTransferObjects\StorePickupResponseData;
use Domain\ShippingMethod\Models\ShippingMethod;

class StorePickupDriver
{
    public function getRate(
        ParcelData $parcelData,
        ShippingAddressData $address,
        ShippingMethod $shippingMethod
    ): RateResponse {
        return new StorePickupResponseData;
    }

    public function getInternationalRate(
        ParcelData $parcelData,
        ShippingAddressData $address,
    ): RateResponse {
        return new StorePickupResponseData;
    }
}
