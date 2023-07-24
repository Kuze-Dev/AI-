<?php

declare(strict_types=1);

namespace Domain\Discount\Actions;

use Domain\Discount\Enums\DiscountAmountType;
use Domain\Discount\Enums\DiscountConditionType;
use Domain\Discount\Models\Discount;
use Exception;

final class DiscountHelperFunctions
{
    public function deductOrderSubtotal(Discount $discount, float $subTotal): ?float
    {

        try {
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
                throw new Exception('Minimum amount requirement not met for the discount.');
            }
        } catch (Exception $e) {
            // Handle the exception here or log it for further investigation.
            // You can return null or any appropriate value as needed.
            return 0;
        }
    }
}
