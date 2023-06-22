<?php

declare(strict_types=1);

namespace Domain\PaymentMethod\DataTransferObjects;

class PaymentMethodData
{
    public function __construct(
        public readonly string $title,
        public readonly string $gateway,
        public readonly string $subtitle,
        public readonly string $description,
        public readonly bool $status,
        public readonly array $credentials = [],
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            title: $data['title'],
            gateway: $data['gateway'],
            subtitle: $data['subtitle'],
            description: $data['description'],
            status: $data['status'],
            credentials: $data['credentials'] ?? [],
        );
    }
}
