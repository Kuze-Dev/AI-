<?php

declare(strict_types=1);

namespace Domain\Address\Actions;

use Domain\Address\Exceptions\CantDeleteDefaultAddressException;
use Domain\Address\Models\Address;

class DeleteAddressAction
{
    public function execute(Address $address): ?bool
    {
        if ($address->is_default_shipping || $address->is_default_billing) {
            throw new CantDeleteDefaultAddressException;
        }

        return $address->delete();
    }
}
