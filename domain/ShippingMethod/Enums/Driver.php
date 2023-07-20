<?php

declare(strict_types=1);

namespace Domain\ShippingMethod\Enums;

use Domain\Shipment\Drivers\StorePickupDriver;
use Domain\Shipment\Drivers\UspsDriver;

enum Driver: string
{
    case USPS = 'usps';
    case STORE_PICKUP = 'store-pickup';
}
