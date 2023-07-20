<?php

declare(strict_types=1);

namespace Domain\ShippingMethod\Actions;

use Domain\ShippingMethod\DataTransferObjects\ShippingMethodData;
use Domain\ShippingMethod\Models\ShippingMethod;

class UpdateShippingMethodAction
{
    /**
     * Execute operations for updating
     * collection and save collection query.
     */
    public function execute(ShippingMethod $shippingMethod, ShippingMethodData $shippingData): ShippingMethod
    {
        $shippingMethod->update([
            'title' => $shippingData->title,
            'subtitle' => $shippingData->subtitle,
            'driver' => $shippingData->driver,
            'status' => $shippingData->status,
            'description' => $shippingData->description,
            'ship_from_address' => $shippingData->ship_from_address,
        ]);

        return $shippingMethod;
    }
}
