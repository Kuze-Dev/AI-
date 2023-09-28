<?php

declare(strict_types=1);

namespace Domain\Service\Actions;

use Domain\Service\DataTransferObjects\ServiceData;
use Domain\Service\Models\Service;
use Illuminate\Database\Eloquent\Model;
use Support\Common\Actions\SyncMediaCollectionAction;
use Support\Common\DataTransferObjects\MediaCollectionData;
use Support\Common\DataTransferObjects\MediaData;
use Support\MetaData\Actions\UpdateMetaDataAction;
use Support\MetaData\DataTransferObjects\MetaDataData;

class UpdateServiceAction
{
    public function __construct(
        protected UpdateMetaDataAction $updateMetaData,
        protected SyncMediaCollectionAction $syncMediaCollection,
    ) {
    }

    public function execute(Service $service, ServiceData $serviceData): Service|Model
    {
        $service->update([
            'name' => $serviceData->name,
            'description' => $serviceData->description,
            'price' => $serviceData->price,
            'billing_cycle' => $serviceData->billing_cycle,
            'recurring_payment' => $serviceData->recurring_payment,
            'data' => $serviceData->data,
            'is_featured' => $serviceData->is_featured,
            'is_special_offer' => $serviceData->is_special_offer,
            'pay_upfront' => $serviceData->pay_upfront,
            'is_subscription' => $serviceData->is_subscription,
            'status' => $serviceData->status,
        ]);

        $service->taxonomyTerms()->sync([$serviceData->taxonomy_term_id]);

        $this->updateMetaData->execute($service, MetaDataData::fromArray($serviceData->meta_data ?? []));

        $media = collect($serviceData->media_collection['media'])->map(function ($material) {
            return new MediaData(media: $material);
        })->toArray();

        $this->syncMediaCollection->execute($service, new MediaCollectionData(
            collection: $serviceData->media_collection['collection'] ?? null,
            media: $media
        ));

        return $service;
    }
}
