<?php

declare(strict_types=1);

namespace Domain\Discount\Actions;

use Domain\Discount\Models\Discount;

class ForceDeleteDiscountAction
{
    /** Execute a delete content query. */
    public function execute(Discount $discount): ?bool
    {
        $discount->discountCondition()->forceDelete();
        $discount->discountRequirement()->forceDelete();

        return $discount->forceDelete();
    }
}
