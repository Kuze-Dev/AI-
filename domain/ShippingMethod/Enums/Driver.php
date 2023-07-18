<?php

declare(strict_types=1);

namespace Domain\ShippingMethod\Enums;

enum Driver: string
{
    case USPS = 'usps';
    case STORE_PICKUP = 'store-pickup';
}
