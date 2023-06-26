<?php

declare(strict_types=1);

namespace Domain\Discount\Enums;

enum DiscountStatus: string
{
    case ACTIVE = 'active';
    case INACTIVE = 'inactive';
}
