<?php

declare(strict_types=1);

namespace Domain\Payments\DataTransferObjects;

use Illuminate\Http\UploadedFile;

class ProofOfPaymentData
{
    public function __construct(
        public readonly UploadedFile|string $proof_of_payment,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            proof_of_payment: $data['proof_of_payment'],
        );
    }
}
