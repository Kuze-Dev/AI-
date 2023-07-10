<?php

declare(strict_types=1);

namespace Domain\Address\DataTransferObjects;

class AddressData
{
    public function __construct(
        public readonly string $label_as,
        public readonly string $address_line_1,
        public readonly int $state_id,
        public readonly string $zip_code,
        public readonly string $city,
        public readonly bool $is_default_shipping,
        public readonly bool $is_default_billing,
        public readonly ?int $customer_id = null,
    ) {
    }

    public static function fromArray(array $data): self
    {
        $data['state_id'] = (int) $data['state_id'];

        return new self(...$data);
    }
}
