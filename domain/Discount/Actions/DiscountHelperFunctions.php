<?php

declare(strict_types=1);

namespace Domain\Discount\Actions;

use Domain\Discount\Enums\DiscountAmountType;
use Domain\Discount\Enums\DiscountConditionType;
use Domain\Discount\Enums\DiscountStatus;
use Domain\Discount\Models\Discount;
use Domain\Discount\Models\DiscountLimit;

final class DiscountHelperFunctions
{
    public function deductableAmount(Discount $discount, float $subTotal, float $shippingTotal): ?float
    {
        $deductable = 0;

        if($discount->discountCondition->discount_type === DiscountConditionType::ORDER_SUB_TOTAL
            && $subTotal >= $discount->discountRequirement?->minimum_amount
            && $discount->discountCondition->amount_type === DiscountAmountType::FIXED_VALUE) {

            $deductable = $discount->discountCondition->amount;

        }

        if($discount->discountCondition->discount_type === DiscountConditionType::ORDER_SUB_TOTAL
            && $subTotal >= $discount->discountRequirement?->minimum_amount
            && $discount->discountCondition->amount_type === DiscountAmountType::PERCENTAGE) {

            $deductable = round($subTotal * $discount->discountCondition->amount / 100, 2);
        }

        if($discount->discountCondition->discount_type === DiscountConditionType::DELIVERY_FEE
            && $subTotal >= $discount->discountRequirement?->minimum_amount
            && $discount->discountCondition->amount_type === DiscountAmountType::FIXED_VALUE) {

            $deductable = $discount->discountCondition->amount;
        }

        if($discount->discountCondition->discount_type === DiscountConditionType::DELIVERY_FEE
            && $subTotal >= $discount->discountRequirement?->minimum_amount
            && $discount->discountCondition->amount_type === DiscountAmountType::PERCENTAGE) {

            $deductable = round($shippingTotal * $discount->discountCondition->amount / 100, 2);
        }

        return $deductable;
    }

    public function validateDiscountCode(?Discount $discount, float $grandTotal): string
    {
        $uses = DiscountLimit::whereCode($discount?->code)->count();

        if ($discount?->status === DiscountStatus::INACTIVE) {
            return 'This discount code is inactive.';
        }

        if ($discount?->valid_end_at && $discount->valid_end_at < now()) {
            return 'This discount code has expired.';
        }

        if ($uses >= $discount?->max_uses) {
            return 'This discount code max usage limit has been reached.';
        }

        if($grandTotal < $discount?->discountRequirement?->minimum_amount) {
            return 'minimum amount required not reached for this discount.';
        }

        return 'Valid discount!';
    }
}
