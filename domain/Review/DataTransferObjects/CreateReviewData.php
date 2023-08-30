<?php

declare(strict_types=1);

namespace Domain\Review\DataTransferObjects;

class CreateReviewData
{
    public function __construct(
        public readonly int $rating,
        public readonly int $order_line_id,
        public readonly bool $is_anonymous,
        public readonly ?string $comment,
        public readonly ?array $media,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            rating: (int) $data['rating'],
            order_line_id: (int) $data['order_line_id'],
            is_anonymous: (bool) $data['is_anonymous'],
            comment: $data['comment'] ?? null,
            media: $data['media'] ?? [],
        );
    }
}
