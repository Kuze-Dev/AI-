<?php

declare(strict_types=1);

namespace Domain\Discount\Actions;

use Domain\Discount\Models\Discount;

class RestoreDiscountAction
{
    /** Execute a delete content query. */
    public function execute(Discount $discount): ?bool
    {
        $discount->discountCondition()->restore();
        $discount->discountRequirement()->restore();

        return $discount->restore();
    }
}
