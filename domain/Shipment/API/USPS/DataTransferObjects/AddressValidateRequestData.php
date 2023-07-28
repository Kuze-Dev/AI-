<?php

declare(strict_types=1);

namespace Domain\Shipment\API\USPS\DataTransferObjects;

use Domain\Address\Models\Address;

class AddressValidateRequestData
{
    public function __construct(
        public readonly string $Address1,
        public readonly string $Address2,
        public readonly string $City,
        public readonly string $State,
        public readonly string $Zip5,
        public readonly string $Zip4,
    ) {
    }

    public static function formAddress(Address $address): self
    {
        return new self(
            Address1: '',
            Address2: $address->address_line_1,
            City: $address->city,
            State: $address->state->name,
            Zip5: $address->zip_code,
            Zip4: '',
        );
    }

    public function toArray(): array
    {
        return get_object_vars($this);
    }
}
