<?php

namespace Domain\Service\DataTransferObjects;

class ServiceData
{
    private function __construct(
        protected readonly int $blueprint_id,
        protected readonly string $name,
        protected readonly ?string $description,
        protected readonly ?int $price,
        protected readonly bool $is_featured,
        protected readonly bool $is_special_offer,
        protected readonly bool $is_subscription,
        protected readonly string $status,
    )
    {
    }

    public static function fromArray(?array $data): self
    {
        return new self(
            blueprint_id: $data['blueprint_id'],
            name: $data['name'],
            description: $data['description'] ?? null,
            price: $data['price'],
            is_featured: $data['is_featured'],
            is_special_offer: $data['is_special_offer'],
            is_subscription: $data['is_subscription'],
            status: $data['status'],
        );
    }
}
