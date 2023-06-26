<?php

declare(strict_types=1);

namespace Domain\Discount\Actions;

use Domain\Discount\DataTransferObjects\DiscountCodeData;
use Domain\Discount\DataTransferObjects\DiscountConditionData;
use Domain\Discount\DataTransferObjects\DiscountData;
use Domain\Discount\Models\Discount;

final class CreateDiscountAction
{
    public function __construct(
        protected CreateDiscountCodeAction $createDiscountCodeAction,
        protected CreateDiscountConditionAction $createDiscountConditionAction,
    ) {
    }

    /** Execute create content query. */
    public function execute(DiscountData $discountData, DiscountConditionData $discountConditionData, DiscountCodeData $discountCodeData): Discount
    {
        $discount = Discount::create([
            'name' => $discountData->name,
            'slug' => $discountData->slug,
            'description' => $discountData->description,
            'type' => $discountData->type,
            'amount' => $discountData->amount,
            'status' => $discountData->status,
            'max_uses' => $discountData->max_uses,
            // 'max_uses_per_user' => $discountData->max_uses_per_user,
            'valid_start_at' => $discountData->valid_start_at,
            'valid_end_at' => $discountData->valid_end_at,
        ]);

        $this->createDiscountCodeAction->execute($discountCodeData);
        $this->createDiscountConditionAction->execute($discountConditionData);

        return $discount;
    }
}
