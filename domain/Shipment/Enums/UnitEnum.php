<?php

declare(strict_types=1);

namespace Domain\Shipment\Enums;

enum UnitEnum: string
{
    case LBS = 'lbs';
    case KG = 'kg';

    case INCH = 'inch';
    case CM = 'cm';

}
