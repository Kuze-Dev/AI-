<?php

declare(strict_types=1);

namespace Domain\Shipment;

use Illuminate\Support\Manager;

class ShippingManager extends Manager
{
    public function getDefaultDriver(): string
    {
        return config('shipping.default');
    }
}
