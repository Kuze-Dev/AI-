<?php

declare(strict_types=1);

namespace Domain\Discount\DataTransferObjects;

use Domain\Discount\Enums\DiscountConditionType;
use Domain\Discount\Models\Discount;

class DiscountConditionData
{
    public function __construct(
        public readonly DiscountConditionType $discount_condition_type,
        public readonly ?array $data = null,
        public readonly int $discount_id,
    ) {
    }
}
