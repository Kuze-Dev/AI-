<?php

declare(strict_types=1);

namespace Domain\ServiceOrder\Enums;

enum ServiceOrderAddressType: string
{
    case SERVICE_ADDRESS = 'service_address';
    case BILLING_ADDRESS = 'billing_address';
}
