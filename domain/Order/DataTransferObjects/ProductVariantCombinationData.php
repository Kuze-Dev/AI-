<?php

declare(strict_types=1);

namespace Domain\Order\DataTransferObjects;

class ProductVariantCombinationData
{
    public function __construct(
        public readonly int $option_id,
        public readonly string $option,
        public readonly int $option_value_id,
        public readonly string $option_value,
        public readonly array|null $option_value_data,
    ) {
    }
}
