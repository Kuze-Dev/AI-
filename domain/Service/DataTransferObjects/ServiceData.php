<?php

declare(strict_types=1);

namespace Domain\Service\DataTransferObjects;

class ServiceData
{
    public function __construct(
        public readonly ?string $blueprint_id,
        public readonly ?int $taxonomy_term_id,
        public readonly string $name,
        public readonly ?string $description,
        public readonly ?int $price,
        public readonly ?string $billing_cycle,
        public readonly ?string $recurring_payment,
        public readonly ?array $data = [],
        public readonly bool $is_featured = false,
        public readonly bool $is_special_offer = false,
        public readonly bool $pay_upfront = false,
        public readonly bool $is_subscription = false,
        public readonly bool $status = false,
        public readonly ?array $meta_data = [],
        public readonly ?array $media_collection = [],
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            blueprint_id: $data['blueprint_id'] ?? null,
            taxonomy_term_id: (int) $data['taxonomy_term_id'],
            name: $data['name'],
            description: $data['description'] ?? null,
            price: (int) $data['price'],
            billing_cycle: $data['billing_cycle'] ?? null,
            recurring_payment: $data['recurring_payment'] ?? null,
            data: $data['data'] ?? [],
            is_featured: $data['is_featured'],
            is_special_offer: $data['is_special_offer'],
            pay_upfront: $data['pay_upfront'],
            is_subscription: $data['is_subscription'],
            status: $data['status'],
            meta_data: $data['meta_data'],
            media_collection: ['collection' => 'image', 'media' => $data['images']],
        );
    }
}
