<?php

declare(strict_types=1);

namespace Domain\Address\Actions;

use Domain\Address\DataTransferObjects\AddressData;
use Domain\Address\Models\Address;

class EditCustomerAddressAction
{
    public function execute(Address $address, AddressData $addressData): Address
    {
        $address->update([
            'state_id' => $addressData->state_id,
            'label_as' => $addressData->label_as,
            'address_line_1' => $addressData->address_line_1,
            'zip_code' => $addressData->zip_code,
            'city' => $addressData->city,
        ]);

        return $address;
    }
}
