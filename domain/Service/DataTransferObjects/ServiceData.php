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
        public readonly ?float $retail_price,
        public readonly ?float $selling_price,
        public readonly ?string $billing_cycle,
        public readonly ?int $due_date_every,
        public readonly bool $is_featured = false,
        public readonly bool $is_special_offer = false,
        public readonly bool $pay_upfront = false,
        public readonly bool $is_subscription = false,
        public readonly bool $status = false,
        public readonly bool $needs_approval = false,
        public readonly bool $is_auto_generated_bill = false,
        public readonly bool $is_partial_payment = false,
        //        public readonly bool $is_installment = false,
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
            retail_price: (float) $data['retail_price'],
            selling_price: (float) $data['selling_price'],
            billing_cycle: $data['billing_cycle'] ?? null,
            due_date_every: array_key_exists('due_date_every', $data)
                ? $data['due_date_every'] !== null ? (int) $data['due_date_every'] : null
                : null,
            is_featured: $data['is_featured'],
            is_special_offer: $data['is_special_offer'],
            pay_upfront: $data['pay_upfront'],
            is_subscription: $data['is_subscription'],
            status: $data['status'],
            needs_approval: $data['needs_approval'],
            is_auto_generated_bill: $data['is_auto_generated_bill'] ?? false,
            is_partial_payment: $data['is_partial_payment'],
            //            is_installment: $data['is_installment'],
            meta_data: $data['meta_data'],
            media_collection: ['collection' => 'media', 'materials' => $data['media']],
        );
    }
}
