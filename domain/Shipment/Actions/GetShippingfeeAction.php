<?php

declare(strict_types=1);

namespace Domain\Shipment\Actions;

use Domain\Shipment\DataTransferObjects\ParcelData;
use Domain\Shipment\DataTransferObjects\ShippingAddressData;
use Domain\ShippingMethod\Models\ShippingMethod;

class GetShippingfeeAction
{
    public function __construct(
        private readonly GetShippingRateAction $getShippingRateAction,
    ) {
    }

    public function execute(
        ParcelData $parcelData,
        ShippingMethod $shippingMethod,
        ShippingAddressData $address,
        int|string|null $serviceID = null
    ): float {

        return $this->getShippingRateAction->execute(
            $parcelData,
            $shippingMethod,
            $address
        )->getRate($serviceID);

    }
}
