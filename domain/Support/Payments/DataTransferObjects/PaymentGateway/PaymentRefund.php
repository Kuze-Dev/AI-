<?php

declare(strict_types=1);

namespace Domain\Support\Payments\DataTransferObjects\PaymentGateway;

class PaymentRefund
{
    public function __construct(
        public bool $success = false,
        public ?string $message = null,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            success: $data['success'],
            message: $data['message'] ?? null,
        );
    }
}
