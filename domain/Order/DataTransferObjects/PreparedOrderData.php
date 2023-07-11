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
        public readonly Address $shipping_address,
        public readonly Address $billing_address,
        public readonly Currency $currency,
        public readonly mixed $cartLine,
        public readonly ?string $notes,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            customer: $data['customer'],
            shipping_address: $data['shipping_address'],
            billing_address: $data['billing_address'],
            currency: $data['currency'],
            cartLine: $data['cartLine'],
            notes: $data['notes'] ?? null
        );
    }
}
