<?php

declare(strict_types=1);

namespace Domain\Product\Enums;

enum DiscountAmountType: string
{
    case FIXED_VALUE = 'fixed_value';
    case PERCENTAGE = 'percentage';
}
