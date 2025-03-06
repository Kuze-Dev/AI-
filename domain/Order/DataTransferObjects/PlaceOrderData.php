<?php

declare(strict_types=1);

namespace Domain\Order\DataTransferObjects;

readonly class PlaceOrderData
{
    public function __construct(
        public PlaceOrderAddressData $addresses,
        public string $cart_reference,
        public string $shipping_method,
        public string $payment_method,
        public ?string $notes,
        public ?string $discountCode,
        public int|string|null $serviceId = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            addresses: new PlaceOrderAddressData(
                shipping: (int) $data['addresses']['shipping'],
                billing: (int) $data['addresses']['billing']
            ),
            cart_reference: $data['cart_reference'],
            shipping_method: $data['shipping_method'],
            payment_method: $data['payment_method'],
            notes: $data['notes'] ?? null,
            discountCode: $data['discount_code'] ?? null,
            serviceId: $data['service_id'] ?? null,
        );
    }
}
