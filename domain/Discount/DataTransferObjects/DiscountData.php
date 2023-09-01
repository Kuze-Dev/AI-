<?php

declare(strict_types=1);

namespace Domain\Discount\DataTransferObjects;

use Carbon\Carbon;
use Domain\Discount\Enums\DiscountAmountType;
use Domain\Discount\Enums\DiscountConditionType;
use Domain\Discount\Enums\DiscountRequirementType;
use Domain\Discount\Enums\DiscountStatus;
use Illuminate\Support\Str;

final class DiscountData
{
    public function __construct(
        public readonly string $name,
        public readonly string $slug,
        public readonly ?string $description = null,
        public readonly string $code,
        public readonly DiscountStatus $status,
        public readonly ?int $max_uses = null,
        public readonly Carbon $valid_start_at,
        public readonly ?Carbon $valid_end_at = null,
        public readonly DiscountConditionData $discountConditionData,
        public readonly ?DiscountRequirementData $discountRequirementData = null,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            slug: Str::slug($data['name']),
            description: $data['description'] ?? null,
            code: $data['code'],
            status: DiscountStatus::from($data['status']),
            max_uses: ! empty($data['max_uses']) ? (int) $data['max_uses'] : null,
            valid_start_at: Carbon::parse($data['valid_start_at']),
            valid_end_at: isset($data['valid_end_at']) ? Carbon::parse($data['valid_end_at']) : null,
            discountConditionData: new DiscountConditionData(
                discount_type: DiscountConditionType::from($data['discountCondition']['discount_type']),
                discount_amount_type: DiscountAmountType::from($data['discountCondition']['amount_type']),
                amount: (int) ($data['discountCondition']['amount'] ?? 0)
            ),
            discountRequirementData: new DiscountRequirementData(
                discount_requirement_type: ! empty($data['discountRequirement']['minimum_amount']) ? DiscountRequirementType::MINIMUM_ORDER_AMOUNT : null,
                // discount_requirement_type: isset($data['discountRequirement']['requirement_type'])
                //     ? DiscountRequirementType::tryFrom($data['discountRequirement']['requirement_type'])
                //     : null,
                minimum_amount: isset($data['discountRequirement']['minimum_amount'])
                    ? (int) $data['discountRequirement']['minimum_amount']
                    : null,
            )
        );
    }
}
