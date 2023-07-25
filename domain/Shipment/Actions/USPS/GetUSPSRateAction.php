<?php

declare(strict_types=1);

namespace Domain\Shipment\Actions\USPS;

use Domain\Address\Models\Address;
use Domain\Customer\Models\Customer;
use Domain\Shipment\Actions\GetShippingRateAction;
use Domain\Shipment\DataTransferObjects\ParcelData;
use Domain\ShippingMethod\Models\ShippingMethod;

class GetUSPSRateAction
{
    public function __construct(
        private readonly GetShippingRateAction $getShippingRateAction,
    ) {
    }

    public function execute(
        Customer $customer,
        ParcelData $parcelData,
        ShippingMethod $shippingMethod,
        Address $address,
        ?int $serviceID = null
    ): float {

        return $this->getShippingRateAction->execute(
            $customer,
            $parcelData,
            $shippingMethod,
            $address
        )
            ->getRate($serviceID);
    }
}
