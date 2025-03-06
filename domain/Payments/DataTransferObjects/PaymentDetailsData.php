<?php

declare(strict_types=1);

namespace Domain\Payments\DataTransferObjects;

class PaymentDetailsData
{
    public function __construct(
        public readonly ?string $subtotal = null,
        public readonly ?string $shipping = null,
        public readonly ?string $tax = null,
        public readonly ?string $handling_fee = null,
        public readonly ?string $shipping_discount = null,
        public readonly ?string $insurance = null,
        public readonly ?string $gift_wrap = null,
        public readonly ?string $fee = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            subtotal: $data['subtotal'] ?? null,
            shipping: $data['shipping'] ?? null,
            tax: $data['tax'] ?? null,
            handling_fee: $data['handling_fee'] ?? null,
            shipping_discount: $data['shipping_discount'] ?? null,
            insurance: $data['insurance'] ?? null,
            gift_wrap: $data['gift_wrap'] ?? null,
            fee: $data['fee'] ?? null,
        );
    }
}
