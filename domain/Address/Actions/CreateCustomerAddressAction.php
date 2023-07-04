<?php

declare(strict_types=1);

namespace Domain\Address\Actions;

use Domain\Address\DataTransferObjects\AddressData;
use Domain\Address\Models\Address;
use Domain\Customer\Models\Customer;

class CreateCustomerAddressAction
{
    public function execute(Customer $customer, AddressData $addressData): Address
    {
        return Address::create([
            'customer_id' => $customer->getKey(),
            'state_id' => $addressData->state_id,
            'label_as' => $addressData->label_as,
            'address_line_1' => $addressData->address_line_1,
            'zip_code' => $addressData->zip_code,
            'city' => $addressData->city,
            'is_default_shipping' => $addressData->is_default_shipping,
            'is_default_billing' => $addressData->is_default_billing,
        ]);
    }
}
