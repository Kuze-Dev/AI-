<?php

declare(strict_types=1);

namespace Domain\Discount\DataTransferObjects;

use Domain\Discount\Enums\DiscountConditionType;
use Domain\Discount\Enums\DiscountStatus;

class DiscountCodeData
{
    public function __construct(
        public readonly string $code,
        public readonly DiscountData $discount,
    ) {
    }
}
