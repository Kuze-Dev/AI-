<?php

declare(strict_types=1);

namespace Domain\ShippingMethod\Enums;

use Domain\Shipment\API\USPS\Clients\AddressClient;
use Domain\Shipment\API\USPS\Clients\RateClient;
use Domain\Shipment\Drivers\StorePickupDriver;
use Domain\Shipment\Drivers\UspsDriver;

enum Driver: string
{
    case USPS = 'usps';
    case STORE_PICKUP = 'store-pickup';

    public function getShipping(): UspsDriver|StorePickupDriver
    {
        return match ($this) {
            Driver::STORE_PICKUP => new StorePickupDriver(),
            Driver::USPS => new UspsDriver(
                app(RateClient::class),
                app(AddressClient::class)
            ),
        };
    }
}
