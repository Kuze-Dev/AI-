<?php

declare(strict_types=1);

namespace Domain\Order\DataTransferObjects;

class GuestPlaceOrderData
{
    public function __construct(
        public readonly string $session_id,
        public readonly GuestCustomerData $customer,
        public readonly GuestPlaceOrderAddressData $addresses,
        public readonly string $cart_reference,
        public readonly string $shipping_method,
        public readonly string $payment_method,
        public readonly ?string $notes,
        public readonly ?string $discountCode,
        public readonly ?int $serviceId,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            session_id: $data['session_id'],
            customer: GuestCustomerData::fromArray($data['customer']),
            addresses: new GuestPlaceOrderAddressData(
                shipping: GuestOrderAddressData::fromArray($data['addresses']['shipping']),
                billing: GuestOrderAddressData::fromArray($data['addresses']['billing'])
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
