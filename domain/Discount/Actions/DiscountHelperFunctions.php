<?php

declare(strict_types=1);

namespace Domain\Discount\Actions;

use Domain\Discount\DataTransferObjects\DiscountMessagesData;
use Domain\Discount\Enums\DiscountAmountType;
use Domain\Discount\Enums\DiscountConditionType;
use Domain\Discount\Enums\DiscountStatus;
use Domain\Discount\Models\Discount;
use Domain\Discount\Models\DiscountCondition;
use Domain\Discount\Models\DiscountRequirement;

final class DiscountHelperFunctions
{
    public function deductableAmount(Discount $discount, float $subTotal, float $shippingTotal): ?float
    {
        $deductable = 0;

        if (
            $discount->discountCondition->discount_type === DiscountConditionType::ORDER_SUB_TOTAL
            && $subTotal >= $discount->discountRequirement?->minimum_amount
            && $discount->discountCondition->amount_type === DiscountAmountType::FIXED_VALUE
        ) {

            $deductable = $discount->discountCondition->amount;
        }

        if (
            $discount->discountCondition->discount_type === DiscountConditionType::ORDER_SUB_TOTAL
            && $subTotal >= $discount->discountRequirement?->minimum_amount
            && $discount->discountCondition->amount_type === DiscountAmountType::PERCENTAGE
        ) {

            $deductable = round($subTotal * $discount->discountCondition->amount / 100, 2);
        }

        if (
            $discount->discountCondition->discount_type === DiscountConditionType::DELIVERY_FEE
            && $subTotal >= $discount->discountRequirement?->minimum_amount
            && $discount->discountCondition->amount_type === DiscountAmountType::FIXED_VALUE
        ) {

            $deductable = $discount->discountCondition->amount;
        }

        if (
            $discount->discountCondition->discount_type === DiscountConditionType::DELIVERY_FEE
            && $subTotal >= $discount->discountRequirement?->minimum_amount
            && $discount->discountCondition->amount_type === DiscountAmountType::PERCENTAGE
        ) {

            $deductable = round($shippingTotal * $discount->discountCondition->amount / 100, 2);
        }

        return $deductable;
    }

    public function validateDiscountCode(?Discount $discount, float $grandTotal): DiscountMessagesData
    {

        if (is_null($discount)) {
            return DiscountMessagesData::fromArray([
                'message' => 'This discount code is invalid.',
            ]);
        }

        $discountAmount = DiscountRequirement::whereBelongsTo($discount)->first();
        $discountCondition = DiscountCondition::whereBelongsTo($discount)->first();

        if ($discount?->status === DiscountStatus::INACTIVE) {
            return DiscountMessagesData::fromArray([
                'message' => 'This discount code is invalid.',
            ]);
        }

        if ($discount?->valid_end_at && $discount->valid_end_at < now()) {
            return DiscountMessagesData::fromArray([
                'message' => 'This discount code has expired.',
            ]);
        }

        if ($discount?->valid_start_at > now()) {
            return DiscountMessagesData::fromArray([
                'message' => 'This discount code is invalid.',
            ]);
        }

        if ($discount?->max_uses == 0) {
            return DiscountMessagesData::fromArray([
                'message' => 'This discount code max usage limit has been reached.',
            ]);
        }

        if ($grandTotal < $discountAmount->minimum_amount) {
            return DiscountMessagesData::fromArray([
                'message' => 'You need to purchase at least '
                    . $discountAmount->minimum_amount  . ' to apply this discount',
            ]);
        }

        return DiscountMessagesData::fromArray([
            'status' => 'valid',
            'message' => 'Discount code applied',
            'amount_type' => $discountCondition->amount_type,
            'amount' => $discountCondition->amount,
            'discount_type' => $discountCondition->discount_type,
        ]);
    }
}
