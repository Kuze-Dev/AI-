<?php

declare(strict_types=1);

namespace Domain\Discount\Enums;

enum DiscountRequirementType: string
{
    case MINIMUM_ORDER_AMOUNT = 'minimum_order_amount';
    // case MINIMUM_ITEM_QUANTITY = 'minimum_item_quantity';
}
