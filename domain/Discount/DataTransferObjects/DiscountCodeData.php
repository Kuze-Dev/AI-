<?php

declare(strict_types=1);

namespace Domain\Discount\DataTransferObjects;

use Domain\Discount\Models\Discount;

class DiscountCodeData
{
    public function __construct(
        public readonly string $code,
        public readonly int $discount_id,
    ) {
    }
}
