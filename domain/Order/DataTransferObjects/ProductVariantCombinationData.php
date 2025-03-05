<?php

declare(strict_types=1);

namespace Domain\Order\DataTransferObjects;

readonly class ProductVariantCombinationData
{
    public function __construct(
        public int $option_id,
        public string $option,
        public int $option_value_id,
        public string $option_value,
        public ?array $option_value_data,
    ) {
    }
}
