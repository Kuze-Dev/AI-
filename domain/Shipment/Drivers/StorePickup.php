<?php

declare(strict_types=1);

namespace Domain\Shipment\Drivers;

class StorePickup
{
    //    protected string $name = 'store-pickup';

    public function getRate(): float
    {
        return 0.00;
    }
}
