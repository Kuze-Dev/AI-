<?php

declare(strict_types=1);

namespace Domain\Order\DataTransferObjects;

class PlaceOrderData
{
    public function __construct(
        public readonly PlaceOrderAddressData $addresses,
        public readonly string $cart_reference,
        public readonly PlaceOrderTaxationData $taxation_data,
        public readonly string $shipping_method,
        public readonly string $payment_method,
        public readonly ?string $notes,
        public readonly ?string $discountCode,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            addresses: new PlaceOrderAddressData(
                shipping: (int) $data['addresses']['shipping'],
                billing: (int) $data['addresses']['billing']
            ),
            cart_reference: $data['cart_reference'],
            taxation_data: new PlaceOrderTaxationData(
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
