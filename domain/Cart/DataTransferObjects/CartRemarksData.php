<?php

declare(strict_types=1);

namespace Domain\Cart\DataTransferObjects;

class CartRemarksData
{
    public function __construct(
        public readonly ?string $notes,
        public readonly ?array $medias,
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
