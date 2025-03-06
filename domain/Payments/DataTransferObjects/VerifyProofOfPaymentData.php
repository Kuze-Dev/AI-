<?php

declare(strict_types=1);

namespace Domain\Payments\DataTransferObjects;

use Illuminate\Http\UploadedFile;

class VerifyProofOfPaymentData
{
    public function __construct(
        public readonly string $remarks,
        public readonly ?string $message = null,
        public readonly UploadedFile|string|null $proof_of_payment = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            proof_of_payment: $data['proof_of_payment'] ?? null,
            remarks: $data['remarks'],
            message: $data['message'] ?? null,
        );
    }
}
