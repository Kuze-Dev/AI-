<?php

declare(strict_types=1);

namespace Domain\Order\DataTransferObjects;

class OrderPaymentMethodData
{
    public function __construct(
        public readonly string $title,
        public readonly string $slug,
        public readonly string $subtitle,
        public readonly string $gateway,
        public readonly ?string $description,
        public readonly ?string $instruction,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            title: $data['title'],
            slug: $data['slug'],
            subtitle: $data['subtitle'],
            gateway: $data['gateway'],
            description: $data['description'] ?? null,
            instruction: $data['instruction'] ?? null,
        );
    }
}
