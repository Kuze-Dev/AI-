<?php

declare(strict_types=1);

namespace Domain\Payments\DataTransferObjects\PaymentGateway;

class PaymentAuthorize
{
    public function __construct(
        public bool $success = false,
        public ?string $message = null,
        public ?string $url = null,
        public ?array $data = [],
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            success: $data['success'],
            message: $data['message'] ?? null,
            url: $data['url'] ?? null,
            data: $data['data'] ?? null
        );
    }
}
