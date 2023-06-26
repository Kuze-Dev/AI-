<?php

declare(strict_types=1);

namespace Domain\Discount\Actions;

use Domain\Discount\DataTransferObjects\DiscountConditionData;
use Domain\Discount\Models\DiscountCondition;

class CreateDiscountConditionAction
{
    /** Execute create content query. */
    public function execute(DiscountConditionData $discountConditionData): DiscountCondition
    {
        $discountCondition = DiscountCondition::create([
            'discount_id' => $discountConditionData->discount,
            'type' => $discountConditionData->type,
            'data' => $discountConditionData->data,
        ]);

        return $discountCondition;
    }
}
