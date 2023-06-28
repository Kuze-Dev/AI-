<?php

declare(strict_types=1);

namespace Domain\Address\DataTransferObjects;

class AddressData
{
    public function __construct(
        public readonly string $address_line_1,
        public readonly ?string $address_line_2,
        public readonly int $country_id,
        public readonly ?int $state_id,
        public readonly ?int $region_id,
        public readonly int $city_id,
        public readonly string $zip_code,
        public readonly bool $is_default_shipping,
        public readonly bool $is_default_billing,
        public readonly ?int $customer_id = null,
    ) {
    }

    public static function fromArray(array $data): self
    {

        $data['country_id'] = (int) $data['country_id'];
        $data['city_id'] = (int) $data['city_id'];

        if (isset($data['state_id'])) {
            $data['state_id'] = (int) $data['state_id'];
        }

        if (isset($data['region_id'])) {
            $data['region_id'] = (int) $data['region_id'];
        }

        $data += [
            'state_id' => null,
            'region_id' => null,
        ];

        return new self(...$data);
    }
}
