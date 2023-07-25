<?php

declare(strict_types=1);

namespace Domain\Cart\DataTransferObjects;

use Domain\Discount\Enums\DiscountAmountType;
use Domain\Discount\Enums\DiscountConditionType;

class DiscountMessagesData
{
    public function __construct(
        public readonly string $status,
        public readonly string $message,
        public readonly ?DiscountAmountType $amount_type,
        public readonly ?float $amount,
        public readonly ?DiscountConditionType $discount_type,

    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            status: $data['status'],
            message: $data['message'],
            amount_type: $data['amount_type'] ?? null,
            amount: $data['amount'] ?? 0,
            discount_type: $data['discount_type'] ?? null,
        );
    }
}
