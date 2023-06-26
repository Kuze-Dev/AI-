<?php

declare(strict_types=1);

namespace Domain\Discount\DataTransferObjects;

use Carbon\Carbon;
use Domain\Discount\Enums\DiscountStatus;
use Domain\Discount\Enums\DiscountType;

class DiscountData
{
    public function __construct(
        public readonly string $name,
        public readonly string $slug,
        public readonly ?string $description = null,
        public readonly DiscountType $type,
        public readonly DiscountStatus $status,
        public readonly float $amount,
        public readonly int $max_uses,
        public readonly int $max_uses_per_user,
        public readonly Carbon $valid_start_at,
        public readonly Carbon $valid_end_at,
    ) {
    }
}
