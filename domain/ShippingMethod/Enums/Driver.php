<?php

declare(strict_types=1);

namespace Domain\ShippingMethod\Enums;

use Domain\Shipment\API\USPS\Clients\AddressClient;
use Domain\Shipment\API\USPS\Clients\RateClient;
use Domain\Shipment\Drivers\StorePickup;
use Domain\Shipment\Drivers\UspsDriver;

enum Driver: string
{
    case USPS = 'usps';
    case STORE_PICKUP = 'store-pickup';

    public function getShipping(): UspsDriver|StorePickup
    {
        return match ($this) {
            Driver::STORE_PICKUP => new StorePickup(),
            Driver::USPS => new UspsDriver(
                app(RateClient::class),
                app(AddressClient::class)
            ),
        };
    }
}
