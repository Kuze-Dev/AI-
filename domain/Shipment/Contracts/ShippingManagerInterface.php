<?php

declare(strict_types=1);

namespace Domain\Shipment\Contracts;

interface ShippingManagerInterface
{
    public function getDefaultDriver(): string;
}
