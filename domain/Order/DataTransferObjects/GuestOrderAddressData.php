<?php

declare(strict_types=1);

namespace Domain\Order\DataTransferObjects;

readonly class GuestOrderAddressData
{
    public function __construct(
        public string $country_id,
        public int $state_id,
        public string $address_line_1,
        public string $zip_code,
        public string $city,
        public string $label_as,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(...$data);
    }
}
