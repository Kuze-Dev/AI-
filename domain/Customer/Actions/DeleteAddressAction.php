<?php

declare(strict_types=1);

namespace Domain\Customer\Actions;

use Domain\Customer\Models\Address;

class DeleteAddressAction
{
    public function execute(Address $address): ?bool
    {
        return $address->delete();
    }
}
