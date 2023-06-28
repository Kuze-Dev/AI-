<?php

declare(strict_types=1);

namespace Domain\Discount\DataTransferObjects;

use Domain\Discount\Enums\DiscountRequirementType;

class DiscountRequirementData
{
    public function __construct(
        public readonly DiscountRequirementType $discount_requirement_type,
        public readonly ?int $minimum_amount
    ) {
    }
}
