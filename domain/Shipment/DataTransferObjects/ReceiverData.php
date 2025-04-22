<?php

declare(strict_types=1);

namespace Domain\Shipment\DataTransferObjects;

use Domain\Customer\Models\Customer;

class ReceiverData
{
    public function __construct(
        public readonly string|int|null $tier_id = null,
        public readonly ?string $last_name = null,
        public readonly ?string $first_name = null,
        public readonly ?string $email = null,
        public readonly ?string $mobile = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            last_name: $data['last_name'] ?? null,
            first_name: $data['first_name'] ?? null,
            email: $data['email'] ?? null,
            tier_id: $data['tier_id'] ?? null,
            mobile: $data['mobile'] ?? null,
        );
    }

    public static function fromCustomerModel(Customer $customer): self
    {
        return new self(
            last_name: $customer->last_name,
            first_name: $customer->first_name,
            email: $customer->email,
            tier_id: $customer->tier_id,
            mobile: $customer->mobile,
        );
    }

    public function toArray(): array
    {
        return array_filter(get_object_vars($this), fn ($value) => ! is_null($value) && $value !== '');
    }
}
