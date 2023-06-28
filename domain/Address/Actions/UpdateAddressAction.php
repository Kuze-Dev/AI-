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
            'country_id' => $addressData->country_id,
            'state_id' => $addressData->state_id,
            'region_id' => $addressData->region_id,
            'city_id' => $addressData->city_id,
            'address_line_1' => $addressData->address_line_1,
            'address_line_2' => $addressData->address_line_2,
            'zip_code' => $addressData->zip_code,
            'is_default_shipping' => $addressData->is_default_shipping,
            'is_default_billing' => $addressData->is_default_billing,
        ]);

        return $address;
    }
}
