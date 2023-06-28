<?php

declare(strict_types=1);

namespace Domain\Discount\DataTransferObjects;

use Carbon\Carbon;
use Domain\Discount\Enums\DiscountAmountType;
use Domain\Discount\Enums\DiscountConditionType;
use Domain\Discount\Enums\DiscountRequirementType;
use Domain\Discount\Enums\DiscountStatus;

final class DiscountData
{
    public function __construct(
        public readonly string $name,
        public readonly string $slug,
        public readonly ?string $description = null,
        public readonly string $code,
        public readonly DiscountStatus $status,
        public readonly int $max_uses,
        // public readonly int $max_uses_per_user,
        public readonly Carbon $valid_start_at,
        public readonly ?Carbon $valid_end_at = null,
        public readonly DiscountConditionTypeData $discountConditionTypeData,
        public readonly DiscountRequirementData $discountRequirementData,

        // public readonly DiscountConditionType $discount_condition_type,
    ) {
    }

    public static function fromArray(array $data)
    {
        return new self(
            name: $data['name'],
            slug: $data['slug'],
            description: $data['description'],
            code:   $data['code'],
            status: DiscountStatus::tryFrom($data['status']),
            max_uses:   (int) $data['max_uses'],
            valid_start_at: Carbon::parse($data['valid_start_at']),
            valid_end_at: Carbon::parse($data['valid_end_at']),
            discountConditionTypeData: new DiscountConditionTypeData(
                discount_type: DiscountConditionType::tryFrom($data['discountCondition']['discount_type']),
                discount_amount_type: DiscountAmountType::tryFrom($data['discountCondition']['amount_type']),
                amount: (int) $data['discountCondition']['amount']
            ),
            discountRequirementData: new DiscountRequirementData(
                discount_requirement_type: DiscountRequirementType::tryFrom($data['discountRequirement']['requirement_type']),
                minimum_amount: (int) $data['discountRequirement']['minimum_amount'],
            )
        );

    }
}
