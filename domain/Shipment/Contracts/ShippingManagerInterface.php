<?php

declare(strict_types=1);

namespace Domain\Shipment\Contracts;

/**
 * @method \Domain\Shipment\Drivers\UspsDriver|\Domain\Shipment\Drivers\StorePickupDriver driver($driver = null)
 */
interface ShippingManagerInterface
{
    public function getDefaultDriver(): string;
}
