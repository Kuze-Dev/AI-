<?php

declare(strict_types=1);

namespace Domain\Shipment\DataTransferObjects;

use Domain\Address\Models\State;

class ShipFromAddressData
{
    public function __construct(
        public readonly ?string $address = null,
        public readonly ?string $city = null,
        public readonly ?string $state = null,
        public readonly ?string $zipcode = null,
        public readonly ?string $country = null,
        public readonly ?string $code = null,
    ) {
    }

    public function toArray(): array
    {
        return get_object_vars($this);
    }

    // public static function fromArray(array $data): self
    // {
    //     $state = State::where('code',$data['State'])->first();

    //     return new self(
    //         address: $data['Address'] ?? null,
    //         city: $data['City'] ?? null,
    //         state: $data['State'] ?? null,
    //         zipcode: $data['zip5'] ?? null,
    //         country: $data['country'] ?? null,
    //         code: $data['code'] ?? null,
    //     );
    // }
}
