<?php

declare(strict_types=1);

namespace Domain\Address\Actions;

use Domain\Address\Models\Address;

class SetAddressAsDefaultShippingAction
{
    public function execute(Address $address): Address
    {
        $address->update([
            'is_default_shipping' => true,
        ]);

        Address::whereKeyNot($address)
            ->update([
                'is_default_shipping' => false,
            ]);

        return $address;
    }
}
