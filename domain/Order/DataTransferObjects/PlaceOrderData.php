<?php

declare(strict_types=1);

namespace Domain\Order\DataTransferObjects;

class PlaceOrderData
{
    public function __construct(
        public readonly OrderAddressData $addresses,
        public readonly string $cart_reference,
        public readonly ?string $notes,
        public readonly ?string $discountCode,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            addresses: new OrderAddressData(
                shipping: (int)$data['addresses']['shipping'],
                billing: (int)$data['addresses']['billing']
            ),
            cart_reference: $data['cart_reference'],
            notes: $data['notes'] ?? null,
            discountCode: $data['discount_code'],
        );
    }
}

class OrderAddressData
{
    public function __construct(
        public readonly int $shipping,
        public readonly int $billing
    ) {
    }
}
