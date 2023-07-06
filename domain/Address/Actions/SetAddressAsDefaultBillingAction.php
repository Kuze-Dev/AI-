<?php

declare(strict_types=1);

namespace Domain\Address\Actions;

use Domain\Address\Models\Address;

class SetAddressAsDefaultBillingAction
{
    public function execute(Address $address): Address
    {
        $address->update([
            'is_default_billing' => true,
        ]);

        Address::whereKeyNot($address)
            ->update([
                'is_default_billing' => false,
            ]);

        return $address;
    }
}
