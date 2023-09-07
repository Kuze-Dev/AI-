<?php

declare(strict_types=1);

namespace Domain\Discount\Enums;

enum DiscountConditionType: string
{
    case ORDER_SUB_TOTAL = 'order_sub_total';
    case DELIVERY_FEE = 'delivery_fee';
}
