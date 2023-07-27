<?php

declare(strict_types=1);

namespace Domain\Shipment\Enums;

enum BoxTypeEnum: string
{
    case BOX = 'box';
    case TUBE = 'tube';
}
