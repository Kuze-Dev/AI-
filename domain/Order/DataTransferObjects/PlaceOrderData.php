<?php

declare(strict_types=1);

namespace Domain\Order\DataTransferObjects;

class PlaceOrderData
{
    public function __construct(
        public readonly OrderAddressData $addresses,
        public readonly array $cart_line_ids,
        public readonly ?string $notes,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            addresses: new OrderAddressData(
                shipping: $data['addresses']['shipping'],
                billing: $data['addresses']['billing']
            ),
            cart_line_ids: $data['cart_line_ids'],
            notes: $data['notes'] ?? null
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
