<?php

declare(strict_types=1);

namespace Domain\Discount\Enums;

enum DiscountAmountType: string
{
    case FIXED_VALUE = 'fixed_value';
    case PERCENTAGE = 'percentage';
}
