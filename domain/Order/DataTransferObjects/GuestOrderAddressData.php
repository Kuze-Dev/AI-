<?php

declare(strict_types=1);

namespace Domain\Order\DataTransferObjects;

class GuestOrderAddressData
{
    public function __construct(
        public readonly string $country_id,
        public readonly int $state_id,
        public readonly string $address_line_1,
        public readonly string $zip_code,
        public readonly string $city,
        public readonly string $label_as,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(...$data);
    }
}
