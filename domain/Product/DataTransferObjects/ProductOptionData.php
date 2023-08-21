<?php

declare(strict_types=1);

namespace Domain\Product\DataTransferObjects;

class ProductOptionData
{
    public function __construct(
        public readonly string $name
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
        );
    }
}
