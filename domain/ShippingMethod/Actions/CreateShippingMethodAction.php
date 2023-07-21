<?php

declare(strict_types=1);

namespace Domain\ShippingMethod\Actions;

use Support\Common\Actions\SyncMediaCollectionAction;
use Support\Common\DataTransferObjects\MediaCollectionData;
use Domain\ShippingMethod\DataTransferObjects\ShippingMethodData;
use Domain\ShippingMethod\Models\ShippingMethod;

class CreateShippingMethodAction
{
    public function __construct(
        protected SyncMediaCollectionAction $syncMediaCollectionAction
    ) {
    }

    public function execute(ShippingMethodData $shippingData): ShippingMethod
    {
        $model = ShippingMethod::create([
            'title' => $shippingData->title,
            'subtitle' => $shippingData->subtitle,
            'driver' => $shippingData->driver,
            'description' => $shippingData->description,
            'ship_from_address' => $shippingData->ship_from_address,
        ]);

        $this->syncMediaCollectionAction->execute(
            $model,
            MediaCollectionData::fromArray([
                'collection' => 'logo',
                'media' => $shippingData->logo
                    ? [
                        'media' => $shippingData->logo,
                        'custom_properties' => ['alt_text' => $model->slug],
                    ]
                    : [],
            ])
        );

        return $model;
    }
}
