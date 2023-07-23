<?php

declare(strict_types=1);

namespace Domain\Cart\DataTransferObjects;

use Domain\Address\Models\Address;
use Domain\Customer\Models\Customer;
use Domain\ShippingMethod\Models\ShippingMethod;

class CartSummaryShippingData
{
    public function __construct(
        public readonly Customer $customer,
        public readonly Address $shippingAddress,
        public readonly ShippingMethod $shippingMethod,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            customer: $data['customer'],
            shippingAddress: $data['shippingAddress'],
            shippingMethod: $data['shippingMethod'],
        );
    }
}
