<?php

declare(strict_types=1);

namespace Domain\Shipment\DataTransferObjects;

use Domain\Address\Models\Country;
use Domain\Address\Models\State;
use Domain\Address\Models\Address;

class ShippingAddressData
{
    public function __construct(
        public readonly string $address,
        public readonly string $city,
        public readonly string $zipcode,
        public readonly string $code,
        public readonly State $state,
        public readonly Country $country,
    ) {
    }

    public static function fromAddressModel(Address $address): self
    {
        return new self(
            address: $address->address_line_1,
            city: $address->city,
            zipcode: $address->zip_code,
            code: $address->state->code,
            state: $address->state,
            country: $address->state->country,
        );
    }

    public function toArray(): array
    {
        return get_object_vars($this);
    }
}
