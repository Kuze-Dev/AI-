<?php

declare(strict_types=1);

namespace Domain\Address\Actions;

use Domain\Address\Models\Address;

class DeleteAddressAction
{
    public function execute(Address $address): ?bool
    {
        return $address->delete();
    }
}
