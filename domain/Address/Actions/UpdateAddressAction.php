<?php

declare(strict_types=1);

namespace Domain\Address\Actions;

use Domain\Address\DataTransferObjects\AddressData;
use Domain\Address\Models\Address;

class UpdateAddressAction
{
    public function execute(Address $address, AddressData $addressData): Address
    {
        $address->update([
            'address_line_1' => $addressData->address_line_1,
            'address_line_2' => $addressData->address_line_2,
            'country' => $addressData->country,
            'state_or_region' => $addressData->state_or_region,
            'city_or_province' => $addressData->city_or_province,
            'zip_code' => $addressData->zip_code,
            'is_default_shipping' => $addressData->is_default_shipping,
            'is_default_billing' => $addressData->is_default_billing,
        ]);

        return $address;
    }
}
