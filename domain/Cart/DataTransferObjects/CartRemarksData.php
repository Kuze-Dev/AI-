<?php

declare(strict_types=1);

namespace Domain\Cart\DataTransferObjects;

readonly class CartRemarksData
{
    public function __construct(
        public ?string $notes,
        public ?array $medias,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            notes: $data['notes'] ?? null,
            medias: $data['media'] ?? null,
        );
    }
}
