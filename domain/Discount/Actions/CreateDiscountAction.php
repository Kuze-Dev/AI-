<?php

declare(strict_types=1);

namespace Domain\Discount\Actions;

use Domain\Discount\DataTransferObjects\DiscountData;
use Domain\Discount\Models\Discount;

final class CreateDiscountAction
{
    /** Execute create content query. */
    public function execute(DiscountData $discountData): Discount
    {
        $discount = Discount::create([
            'name' => $discountData->name,
            'slug' => $discountData->slug,
            'description' => $discountData->description,
            'code' => $discountData->code,
            'status' => $discountData->status,
            'max_uses' => $discountData->max_uses,
            // 'max_uses_per_user' => $discountData->max_uses_per_user,
            'valid_start_at' => $discountData->valid_start_at,
            'valid_end_at' => $discountData->valid_end_at,
        ]);

        $discount->discountCondition()->create([
            'discount_type' => $discountData->discountConditionTypeData->discount_type,
            'amount_type' => $discountData->discountConditionTypeData->discount_amount_type,
            'amount' => $discountData->discountConditionTypeData->amount,
        ]);

        $discount->discountRequirement()->create([
            'requirement_type' => $discountData->discountRequirementData->discount_requirement_type,
            'minimum_amount' => $discountData->discountRequirementData->minimum_amount,
        ]);

        return $discount;
    }
}
