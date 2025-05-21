<?php

declare(strict_types=1);

namespace Domain\ServiceOrder\DataTransferObjects;

class ServiceBankTransferData
{
    public function __construct(
        public readonly string $reference_id,
        public readonly string $proof_of_payment,
        public readonly ?string $notes,
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            reference_id: $data['referenceId'],
            proof_of_payment: $data['proofOfPayment'],
            notes: $data['notes'],
        );
    }
}
