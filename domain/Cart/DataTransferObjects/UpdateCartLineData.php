<?php

declare(strict_types=1);

namespace Domain\Cart\DataTransferObjects;

class UpdateCartLineData
{
    public function __construct(
        public readonly ?int $quantity,
        public readonly mixed $remarks,
        public readonly ?array $medias,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            quantity: $data['quantity'] ?? null,
            remarks: $data['remarks'] ?? null,
            medias: $data['media'] ?? null,
        );
    }
}
