<?php

declare(strict_types=1);

namespace Domain\Address\Actions;

use Domain\Address\DataTransferObjects\AddressData;
use Domain\Address\Models\Address;

readonly class CreateAddressAction
{
    public function __construct(
        private SetAddressAsDefaultShippingAction $setAddressAsDefaultShipping,
        private SetAddressAsDefaultBillingAction $setAddressAsDefaultBilling,
    ) {
    }

    public function execute(AddressData $addressData): Address
    {
        $address = Address::create([
            'customer_id' => $addressData->customer_id,
            'state_id' => $addressData->state_id,
            'label_as' => $addressData->label_as,
            'address_line_1' => $addressData->address_line_1,
            'zip_code' => $addressData->zip_code,
            'city' => $addressData->city,
            'is_default_shipping' => $addressData->is_default_shipping,
            'is_default_billing' => $addressData->is_default_billing,
        ]);

        if ($addressData->is_default_shipping) {
            $this->setAddressAsDefaultShipping->execute($address);
        }

        if ($addressData->is_default_billing) {
            $this->setAddressAsDefaultBilling->execute($address);
        }

        return $address;
    }
}
