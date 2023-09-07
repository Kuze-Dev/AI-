<?php

declare(strict_types=1);

namespace Domain\Order\DataTransferObjects;

class ProductVariantCombinationData
{
    public function __construct(
        public readonly string $option,
        public readonly string $option_value
    ) {
    }
}
