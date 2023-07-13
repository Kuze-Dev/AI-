<?php

declare(strict_types=1);

namespace Domain\Shipment\DataTransferObjects;

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

    public static function fromArray(array $data): self
    {
        return new self(...$data);
    }
}
