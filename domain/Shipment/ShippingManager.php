<?php

declare(strict_types=1);

namespace Domain\Shipment;

use Illuminate\Support\Manager;

class ShippingManager extends Manager
{
    /**
     * Get the default driver name.
     *
     * @return string
     */
    public function getDefaultDriver(): string
    {
        return config('shipping.default');
    }
}
