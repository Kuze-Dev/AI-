<?php

declare(strict_types=1);

namespace Domain\Discount\Actions;

use Domain\Discount\DataTransferObjects\DiscountMessagesData;
use Domain\Discount\Enums\DiscountAmountType;
use Domain\Discount\Enums\DiscountConditionType;
use Domain\Discount\Enums\DiscountStatus;
use Domain\Discount\Models\Discount;
use Domain\Discount\Models\DiscountCondition;
use Domain\Discount\Models\DiscountLimit;
use Domain\Discount\Models\DiscountRequirement;
use Domain\Order\Models\Order;

final class DiscountHelperFunctions
{
    public function deductableAmount(Discount $discount, float $subTotal, float $shippingTotal): float
    {
        $deductable = 0;

        $discountCondition = $discount->discountCondition;
        $discountRequirement = $discount->discountRequirement;

        if (
            $subTotal >= ($discountRequirement?->minimum_amount ?? 0) &&
            ($discountCondition?->discount_type === DiscountConditionType::ORDER_SUB_TOTAL ||
                $discountCondition?->discount_type === DiscountConditionType::DELIVERY_FEE)
        ) {
            if ($discountCondition->amount_type === DiscountAmountType::FIXED_VALUE) {
                $deductable = $discountCondition->amount;
            } elseif ($discountCondition->amount_type === DiscountAmountType::PERCENTAGE) {
                $amountToApply = ($discountCondition->discount_type === DiscountConditionType::ORDER_SUB_TOTAL) ? $subTotal : $shippingTotal;
                $deductable = round($amountToApply * $discountCondition->amount / 100, 2);
            }
        }

        return $deductable;
    }

    public function validateDiscountCode(?Discount $discount, float $grandTotal): DiscountMessagesData
    {

        if (is_null($discount)) {
            return DiscountMessagesData::fromArray(['message' => 'This discount code is invalid.']);
        }

        $discountAmount = DiscountRequirement::whereBelongsTo($discount)->first();
        $discountCondition = DiscountCondition::whereBelongsTo($discount)->first();

        $validationChecks = [
            'status' => [
                'condition' => $discount->status === DiscountStatus::INACTIVE,
                'message' => 'This discount code is invalid.',
            ],
            'valid_end_at' => [
                'condition' => $discount->valid_end_at && $discount->valid_end_at < now(),
                'message' => 'This discount code has expired.',
            ],
            'valid_start_at' => [
                'condition' => $discount->valid_start_at > now(),
                'message' => 'This discount code is invalid.',
            ],
            'max_uses' => [
                'condition' => $discount->max_uses === 0 || $discount->max_uses === null,
                'message' => 'This discount code max usage limit has been reached.',
            ],
            'grandTotal' => [
                'condition' => $grandTotal < $discountAmount?->minimum_amount,
                'message' => 'You need to purchase at least '.$discountAmount?->minimum_amount.' to apply this discount',
            ],
        ];

        foreach ($validationChecks as $check) {
            if ($check['condition']) {
                return DiscountMessagesData::fromArray(['message' => $check['message']]);
            }
        }

        return DiscountMessagesData::fromArray([
            'status' => 'valid',
            'message' => 'Discount code applied',
            'amount_type' => $discountCondition?->amount_type,
            'amount' => $discountCondition?->amount,
            'discount_type' => $discountCondition?->discount_type,
        ]);
    }

    public function resetDiscountUsage(Order $order): void
    {
        if ($order->discount_code !== null) {
            DiscountLimit::whereOrderId($order->id)->delete();

            /** @var \Domain\Discount\Models\Discount $discount */
            $discount = Discount::whereCode($order->discount_code)->first();

            $discount->update([
                'max_uses' => $discount->max_uses + 1,
            ]);
        }
    }
}
