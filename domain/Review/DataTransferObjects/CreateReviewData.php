<?php

declare(strict_types=1);

namespace Domain\Review\DataTransferObjects;

class ReviewData
{
    public function __construct(
        public readonly int $customer_id,
        public readonly int $product_id,
        public readonly int $rating,
        public readonly string $comment,
        public readonly int $order_line_id,
        public readonly bool $is_anonymous,
        public readonly array $media,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            customer_id: (int) $data['customer_id'],
            product_id: (int) $data['product_id'],
            rating: (int) $data['rating'],
            comment: $data['comment'],
            order_line_id: (int) $data['order_line_id'],
            is_anonymous: (bool) $data['is_anonymous'],
            media: $data['media'] ?? [],
        );
    }
}
