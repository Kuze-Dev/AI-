<?php

declare(strict_types=1);

namespace Domain\Shipment\API\USPS\DataTransferObjects;

class AddressValidateResponseData
{
    public function __construct(
        public readonly string $state,
        public readonly string $zip4,
        public readonly string $zip5,
        public readonly string $city,
        public readonly ?string $address1 = null,
        public readonly ?string $address2 = null,
    ) {}

    public static function fromArray(array $data): self
    {
        $data = $data['AddressValidateResponse']['Address'];

        return new self(
            state: $data['State'],
            zip4: $data['Zip4'],
            zip5: $data['Zip5'],
            city: $data['City'],
            address1: $data['Address1'] ?? null,
            address2: $data['Address2'] ?? null,
        );
    }

    public function toArray(): array
    {
        return get_object_vars($this);
    }
}
