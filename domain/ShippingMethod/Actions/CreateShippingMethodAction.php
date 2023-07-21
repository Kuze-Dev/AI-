<?php

declare(strict_types=1);

namespace Domain\ShippingMethod\Actions;

use Domain\ShippingMethod\DataTransferObjects\ShippingMethodData;
use Domain\ShippingMethod\Models\ShippingMethod;

class CreateShippingMethodAction
{
    public function execute(ShippingMethodData $shippingData): ShippingMethod
    {
        return ShippingMethod::create([
            'title' => $shippingData->title,
            'subtitle' => $shippingData->subtitle,
            'driver' => $shippingData->driver,
            'description' => $shippingData->description,
            'ship_from_address' => $shippingData->ship_from_address,
        ]);
    }
}
