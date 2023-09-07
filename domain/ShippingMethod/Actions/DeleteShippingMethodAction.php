<?php

declare(strict_types=1);

namespace Domain\ShippingMethod\Actions;

use Domain\ShippingMethod\Models\ShippingMethod;

class DeleteShippingMethodAction
{
    /** Execute a delete collection query. */
    public function execute(ShippingMethod $shippingMethod): ?bool
    {
        return $shippingMethod->delete();
    }
}
