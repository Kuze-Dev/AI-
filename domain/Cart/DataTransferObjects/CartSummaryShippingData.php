<?php

declare(strict_types=1);

namespace Domain\Cart\DataTransferObjects;

use Domain\Address\Models\Address;
use Domain\Customer\Models\Customer;
use Domain\ShippingMethod\Models\ShippingMethod;

readonly class CartSummaryShippingData
{
    public function __construct(
        public Customer $customer,
        public ?Address $shippingAddress,
        public ?ShippingMethod $shippingMethod,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            customer: $data['customer'],
            shippingAddress: $data['shippingAddress'] ?? null,
            shippingMethod: $data['shippingMethod'] ?? null,
        );
    }
}
