<?php

declare(strict_types=1);

namespace Domain\Shipment\API\USPS\Enums;

enum Size: string
{
    case LARGE = 'LARGE';
    case REGULAR = 'REGULAR';
}
