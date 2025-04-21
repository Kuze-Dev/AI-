<?php

declare(strict_types=1);

namespace Domain\Shipment;

use Domain\Shipment\Contracts\ShippingManagerInterface;
use Illuminate\Support\Manager;

class ShippingManager extends Manager implements ShippingManagerInterface
{
    #[\Override]
    public function getDefaultDriver(): string
    {
        return config('domain.shipment.default');
    }
}
