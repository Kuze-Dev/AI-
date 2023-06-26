<?php

declare(strict_types=1);

namespace Domain\Discount\Enums;

enum DiscountType: string
{
    case FIXED_VALUE = 'fixed_value';
    case PERCENTAGE = 'percentage';
}
