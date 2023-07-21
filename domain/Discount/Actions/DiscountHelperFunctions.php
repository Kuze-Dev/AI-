<?php

declare(strict_types=1);

namespace Domain\Discount\Actions;

use Domain\Discount\Enums\DiscountAmountType;
use Domain\Discount\Enums\DiscountConditionType;
use Domain\Discount\Models\Discount;

final class DiscountHelperFunctions
{
    public function deductOrderSubtotal(Discount $discount, float $subTotal): ?float
    {

        if (
            $discount->discountCondition->discount_type === DiscountConditionType::ORDER_SUB_TOTAL
            && $subTotal >= $discount->discountRequirement?->minimum_amount
        ) {
            if ($discount->discountCondition->amount_type === DiscountAmountType::FIXED_VALUE) {
                return $discount->discountCondition->amount;
            } elseif ($discount->discountCondition->amount_type === DiscountAmountType::PERCENTAGE) {
                $deductable = $subTotal * ($discount->discountCondition->amount / 100);

                return $deductable;
            }
        } else {
            return 0;
        }
    }
}
