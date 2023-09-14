<?php

declare(strict_types=1);

namespace Domain\Order\DataTransferObjects;

use Domain\Currency\Models\Currency;
use Domain\Discount\Models\Discount;
use Domain\PaymentMethod\Models\PaymentMethod;
use Domain\ShippingMethod\Models\ShippingMethod;
use Domain\Taxation\Models\TaxZone;

class GuestPreparedOrderData
{
    public function __construct(
        public readonly GuestCustomerData $customer,
        public readonly GuestOrderAddressData $shippingAddress,
        public readonly GuestOrderAddressData $billingAddress,
        public readonly Currency $currency,
        public readonly ShippingMethod $shippingMethod,
        public readonly PaymentMethod $paymentMethod,
        public readonly mixed $cartLine,
        public readonly ?TaxZone $taxZone,
        public readonly ?string $notes,
        public readonly ?Discount $discount,
    ) {
    }

    public static function fromArray(array $data): self
    {

        return new self(
            customer: $data['customer'],
            shippingAddress: $data['shippingAddress'],
            billingAddress: $data['billingAddress'],
            currency: $data['currency'],
            taxZone: $data['taxZone'] ?? null,
            paymentMethod: $data['paymentMethod'],
            shippingMethod: $data['shippingMethod'],
            cartLine: $data['cartLine'],
            notes: $data['notes'] ?? null,
            discount: $data['discount'] ?? null,
        );
    }
}
