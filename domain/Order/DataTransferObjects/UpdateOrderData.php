<?php

declare(strict_types=1);

namespace Domain\Order\DataTransferObjects;

class UpdateOrderData
{
    public function __construct(
        public readonly ?string $status,
        public readonly ?string $notes,
        public readonly ?array $bank_proof_medias,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            status: $data['status'] ?? null,
            notes: $data['notes'] ?? null,
            bank_proof_medias: $data['bank_proof_media'] ?? null
        );
    }
}
