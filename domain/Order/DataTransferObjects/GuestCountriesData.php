<?php

declare(strict_types=1);

namespace Domain\Order\DataTransferObjects;

use Domain\Address\Models\Country;

readonly class GuestCountriesData
{
    public function __construct(
        public Country $shippingCountry,
        public Country $billingCountry,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            shippingCountry: $data['shippingCountry'],
            billingCountry: $data['billingCountry'],
        );
    }
}
