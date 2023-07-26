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
            'active' => $shippingData->active,
            'description' => $shippingData->description,
            'shipper_country_id' => $shippingData->shipper_country_id,
            'shipper_state_id' => $shippingData->shipper_state_id,
            'shipper_address' => $shippingData->shipper_address,
            'shipper_city' => $shippingData->shipper_city,
            'shipper_zipcode' => $shippingData->shipper_zipcode,

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
