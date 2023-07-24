<?php

declare(strict_types=1);

namespace Domain\Order\DataTransferObjects;

class PlaceOrderData
{
    public function __construct(
        public readonly OrderAddressData $addresses,
        public readonly string $cart_reference,
        public readonly OrderTaxationData $taxation_data,
        public readonly string $shipping_method,
        public readonly string $payment_method,
        public readonly ?string $notes,
        public readonly ?string $discountCode,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            addresses: new OrderAddressData(
                shipping: (int) $data['addresses']['shipping'],
                billing: (int) $data['addresses']['billing']
            ),
            cart_reference: $data['cart_reference'],
            taxation_data: new OrderTaxationData(
                country_id: (int) $data['taxations']['country_id'],
                state_id: $data['taxations']['state_id'] ?? null
            ),
            shipping_method: $data['shipping_method'],
            payment_method: $data['payment_method'],
            notes: $data['notes'] ?? null,
            discountCode: $data['discount_code'] ?? null,
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

class OrderTaxationData
{
    public function __construct(
        public readonly int $country_id,
        public readonly ?int $state_id
    ) {
    }
}
