<?php

declare(strict_types=1);

namespace Domain\Discount\DataTransferObjects;

use Domain\Discount\Enums\DiscountAmountType;
use Domain\Discount\Enums\DiscountConditionType;

class DiscountConditionData
{
    public function __construct(
        public readonly DiscountConditionType $discount_type,
        public readonly DiscountAmountType $discount_amount_type,
        public readonly int $amount
    ) {
    }
}
