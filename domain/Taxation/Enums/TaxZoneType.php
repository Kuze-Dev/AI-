<?php

declare(strict_types=1);

namespace Domain\Taxation\Enums;

enum TaxZoneType: string
{
    case COUNTRY = 'country';
    case STATE = 'state';
}
