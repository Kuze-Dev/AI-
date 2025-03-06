<?php

declare(strict_types=1);

namespace Domain\Order\DataTransferObjects;

use Domain\Address\Models\Address;
use Domain\Currency\Models\Currency;
use Domain\Customer\Models\Customer;
use Domain\Discount\Models\Discount;
use Domain\PaymentMethod\Models\PaymentMethod;
use Domain\ShippingMethod\Models\ShippingMethod;
use Domain\Taxation\Models\TaxZone;

readonly class PreparedOrderData
{
    public function __construct(
        public Customer $customer,
        public Address $shippingAddress,
        public Address $billingAddress,
        public Currency $currency,
        public ShippingMethod $shippingMethod,
        public PaymentMethod $paymentMethod,
        public mixed $cartLine,
        public ?TaxZone $taxZone,
        public ?string $notes,
        public ?Discount $discount,
    ) {}

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
