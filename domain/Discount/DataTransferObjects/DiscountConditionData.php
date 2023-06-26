<?php

declare(strict_types=1);

namespace Domain\Discount\DataTransferObjects;

use Domain\Discount\Enums\DiscountConditionType;
use Domain\Discount\Models\Discount;

class DiscountConditionData
{
    public function __construct(
        public readonly DiscountConditionType $type,
        public readonly ?string $data = null,
        public readonly Discount $discount,
    ) {
    }
}
