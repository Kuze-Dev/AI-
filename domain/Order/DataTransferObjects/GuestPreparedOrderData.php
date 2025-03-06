<?php

declare(strict_types=1);

namespace Domain\Order\DataTransferObjects;

use Domain\Currency\Models\Currency;
use Domain\Discount\Models\Discount;
use Domain\PaymentMethod\Models\PaymentMethod;
use Domain\Shipment\DataTransferObjects\ReceiverData;
use Domain\Shipment\DataTransferObjects\ShippingAddressData;
use Domain\ShippingMethod\Models\ShippingMethod;
use Domain\Taxation\Models\TaxZone;

readonly class GuestPreparedOrderData
{
    public function __construct(
        public GuestCustomerData $customer,
        public GuestOrderAddressData $shippingAddress,
        public GuestOrderAddressData $billingAddress,
        public Currency $currency,
        public ShippingMethod $shippingMethod,
        public ReceiverData $shippingReceiverData,
        public ShippingAddressData $shippingAddressData,
        public PaymentMethod $paymentMethod,
        public mixed $cartLine,
        public GuestCountriesData $countries,
        public GuestStatesData $states,
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
            shippingReceiverData: $data['shippingReceiverData'],
            shippingAddressData: $data['shippingAddressData'],
            cartLine: $data['cartLine'],
            countries: $data['countries'],
            states: $data['states'],
            notes: $data['notes'] ?? null,
            discount: $data['discount'] ?? null,
        );
    }
}
