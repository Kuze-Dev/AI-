<?php

declare(strict_types=1);

namespace Domain\Favorite\DataTransferObjects;

class FavoriteData
{
    public function __construct(
        public readonly int $customer_id,
        public readonly int $product_id,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            customer_id: (int) $data['customer_id'],
            product_id: (int) $data['product_id'],
        );
    }
}
