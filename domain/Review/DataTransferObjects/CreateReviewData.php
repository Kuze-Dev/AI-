<?php

declare(strict_types=1);

namespace Domain\Review\DataTransferObjects;

readonly class CreateReviewData
{
    public function __construct(
        public int $rating,
        public int $order_line_id,
        public bool $is_anonymous,
        public ?string $comment,
        public ?array $media,
    ) {}

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
