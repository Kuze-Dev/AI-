<?php

declare(strict_types=1);

namespace Domain\Discount\Actions;

use Domain\Discount\DataTransferObjects\DiscountCodeData;
use Domain\Discount\Models\DiscountCode;

class CreateDiscountCodeAction
{
    /** Execute create content query. */
    public function execute(DiscountCodeData $discountCodeData): DiscountCode
    {
        $discountCode = DiscountCode::create([
            'discount_id' => $discountCodeData->discount_id,
            'code' => $discountCodeData->code,

        ]);

        return $discountCode;
    }
}
