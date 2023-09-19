<?php

declare(strict_types=1);

namespace Domain\Address\DataTransferObjects;

use Domain\Customer\Models\Customer;

class AddressData
{
    public function __construct(
        public readonly int $state_id,
        public readonly string $label_as,
        public readonly string $address_line_1,
        public readonly string $zip_code,
        public readonly string $city,
        public readonly ?bool $is_default_shipping = null,
        public readonly ?bool $is_default_billing = null,
        public readonly ?int $customer_id = null,
    ) {
    }

    public static function fromArray(array $data): self
    {
        $data['customer_id'] = (int) $data['customer_id'];
        $data['state_id'] = (int) $data['state_id'];

        return new self(...$data);
    }

    public static function fromAddressAddCustomer(Customer $customer, self $addressData): self
    {
        return new self(
            state_id: $addressData->state_id,
            label_as: $addressData->label_as,
            address_line_1: $addressData->address_line_1,
            zip_code: $addressData->zip_code,
            city: $addressData->city,
            is_default_shipping: $addressData->is_default_shipping,
            is_default_billing: $addressData->is_default_billing,
            customer_id: $customer->getKey(),
        );
    }
}
