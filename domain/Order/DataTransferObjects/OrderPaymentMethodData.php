<?php

declare(strict_types=1);

namespace Domain\Order\DataTransferObjects;

readonly class OrderPaymentMethodData
{
    public function __construct(
        public string $title,
        public string $slug,
        public string $subtitle,
        public string $gateway,
        public ?string $description,
        public ?string $instruction,
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
