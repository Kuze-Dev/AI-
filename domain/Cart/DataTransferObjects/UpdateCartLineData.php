<?php

declare(strict_types=1);

namespace Domain\Cart\DataTransferObjects;

class UpdateCartLineData
{
    public function __construct(
        public readonly ?int $quantity,
        public readonly ?CartRemarksData $remarks,
        public readonly ?array $medias,
    ) {
    }

    public static function fromArray(array $data): self
    {
        $remarksData = isset($data['remarks']) ? CartRemarksData::fromArray($data['remarks']) : null;

        return new self(
            quantity: $data['quantity'] ?? null,
            remarks: $remarksData,
            medias: $data['media'] ?? null,
        );
    }
}
