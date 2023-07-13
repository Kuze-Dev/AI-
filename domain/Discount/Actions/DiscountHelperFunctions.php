<?php

declare(strict_types=1);

namespace Domain\Discount\Actions;

use Domain\Discount\Enums\DiscountAmountType;
use Domain\Discount\Enums\DiscountConditionType;
use Domain\Discount\Enums\DiscountStatus;
use Domain\Discount\Models\Discount;

final class DiscountHelperFunctions
{
    public function deductOrderSubtotalByFixedValue(string $code): ?int
    {
        $discount = Discount::whereCode($code)
            ->whereStatus(DiscountStatus::ACTIVE)
            ->where(function ($query) {
                $query->where('max_uses', '>', 0);
            })
            ->first();

        return $discount?->discountCondition->amount_type === DiscountAmountType::FIXED_VALUE
            && $discount?->discountCondition->discount_type === DiscountConditionType::ORDER_SUB_TOTAL
            ? $discount->discountCondition->amount
            : null;

    }

    public function deductOrderSubtotalByPercentageValue(string $code, float $total): ?float
    {
        $discount = Discount::whereCode($code)
            ->whereStatus(DiscountStatus::ACTIVE)
            ->where(function ($query) {
                $query->where('max_uses', '>', 0);
            })
            ->first();
        $deductable = $total * ($discount->discountCondition->amount / 100);

        return $discount?->discountCondition->amount_type === DiscountAmountType::PERCENTAGE
            && $discount?->discountCondition->discount_type === DiscountConditionType::ORDER_SUB_TOTAL
            ? $deductable
            : null;

    }
}
