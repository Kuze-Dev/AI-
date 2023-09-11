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
            'description' => $discountData->description ?? null,
            'code' => $discountData->code,
            'status' => $discountData->status,
            'max_uses' => $discountData->max_uses,
            'valid_start_at' => $discountData->valid_start_at,
            'valid_end_at' => $discountData->valid_end_at ?? null,
        ]);

        $discount->discountCondition()->create([
            'discount_type' => $discountData->discountConditionData->discount_type ?? null,
            'amount_type' => $discountData->discountConditionData->discount_amount_type ?? null,
            'amount' => $discountData->discountConditionData->amount ?? null,
        ]);

        $discount->discountRequirement()->create([
            'requirement_type' => $discountData->discountRequirementData->discount_requirement_type ?? null,
            'minimum_amount' => $discountData->discountRequirementData->minimum_amount ?? null,
        ]);

        return $discount;
    }
}
