<?php

declare(strict_types=1);

namespace Domain\Order\DataTransferObjects;

use Domain\Address\Models\Address;
use Domain\Currency\Models\Currency;
use Domain\Customer\Models\Customer;

class PreparedOrderData
{
    public function __construct(
        public readonly Customer $customer,
        public readonly Address $shippingAddress,
        public readonly Address $billingAddress,
        public readonly Currency $currency,
        public readonly mixed $cartLine,
        public readonly ?string $notes,
        public readonly ?string $discountCode,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            customer: $data['customer'],
            shippingAddress: $data['shippingAddress'],
            billingAddress: $data['billingAddress'],
            currency: $data['currency'],
            cartLine: $data['cartLine'],
            notes: $data['notes'] ?? null,
            discountCode: $data['discountCode'] ?? null,
        );
    }
}
