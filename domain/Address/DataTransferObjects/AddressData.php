<?php

declare(strict_types=1);

namespace Domain\Address\DataTransferObjects;

class AddressData
{
    public function __construct(
        public readonly string $address_line_1,
        public readonly ?string $address_line_2,
        public readonly string $country,
        public readonly ?string $state_or_region,
        public readonly string $city_or_province,
        public readonly string $zip_code,
        public readonly bool $is_default_shipping,
        public readonly bool $is_default_billing,
        public readonly ?int $customer_id = null,
    ) {
    }
}
