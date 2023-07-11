<?php

declare(strict_types=1);

namespace Domain\Discount\Actions;

use Domain\Discount\Models\Discount;

class SoftDeleteDiscountAction
{
    /** Execute a delete content query. */
    public function execute(Discount $discount): ?bool
    {
        $discount->discountCondition()->delete();
        $discount->discountRequirement()->delete();

        return $discount->delete();
    }
}
