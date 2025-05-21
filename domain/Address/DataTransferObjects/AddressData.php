<?php

declare(strict_types=1);

namespace Domain\Address\DataTransferObjects;

use Domain\Customer\Models\Customer;

readonly class AddressData
{
    public function __construct(
        public int $state_id,
        public string $label_as,
        public string $address_line_1,
        public string $zip_code,
        public string $city,
        public ?bool $is_default_shipping = null,
        public ?bool $is_default_billing = null,
        public ?int $customer_id = null,
    ) {}

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
