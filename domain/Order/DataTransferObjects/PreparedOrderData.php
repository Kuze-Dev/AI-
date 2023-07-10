<?php

declare(strict_types=1);

namespace Domain\Order\DataTransferObjects;

use Domain\Address\Models\Address;
use Domain\Currency\Models\Currency;
use Domain\Customer\Models\Customer;

class PreparedOrderData
{
    public function __construct(
        public readonly Customer $customer,
        public readonly Address $shipping_address,
        public readonly Address $billing_address,
        public readonly Currency $currency,
        public readonly OrderTotalsData $totals,
        public readonly ?string $notes,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            customer: $data['customer'],
            shipping_address: $data['shipping_address'],
            billing_address: $data['billing_address'],
            currency: $data['currency'],
            totals: new OrderTotalsData(
                sub_total: $data['totals']['sub_total'],
                shipping_total: $data['totals']['shipping_total']
            ),
            notes: $data['notes'] ?? null
        );
    }
}

class OrderTotalsData
{
    public function __construct(
        public readonly float $sub_total,
        public readonly float $shipping_total
    ) {
    }
}
