<?php

declare(strict_types=1);

namespace Domain\Shipment\DataTransferObjects;

use Domain\Address\Models\Address;
use Domain\Address\Models\Country;
use Domain\Address\Models\State;

class ShippingAddressData
{
    public function __construct(
        public readonly string $address,
        public readonly string $city,
        public readonly string $zipcode,
        public readonly string $code,
        public readonly State $state,
        public readonly Country $country,
    ) {}

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

    public static function fromArray(array $data): self
    {
        /** @var \Domain\Address\Models\State */
        $state = State::findorFail($data['state']);

        /** @var \Domain\Address\Models\Country */
        $country = Country::findorFail($data['country']);

        return new self(
            address: $data['address'],
            city: $data['city'],
            zipcode: $data['zipcode'],
            code: $state->code,
            state: $state,
            country: $country,
        );
    }

    public static function fromRequestData(array $data): self
    {
        /** @var \Domain\Address\Models\State */
        $state = State::findorFail($data['state']);

        /** @var \Domain\Address\Models\Country */
        $country = Country::where('code', $data['country'])->first();

        return new self(
            address: $data['address'],
            city: $data['city'],
            zipcode: $data['zipcode'],
            code: $state->code,
            state: $state,
            country: $country,
        );
    }

    public function toArray(): array
    {
        return get_object_vars($this);
    }
}
