<?php

declare(strict_types=1);

namespace Domain\Order\Enums;

enum OrderAddressTypes: string
{
    case SHIPPING = 'Shipping';
    case BILLING = 'Billing';
}
