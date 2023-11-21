<?php

declare(strict_types=1);

namespace Domain\Service\Actions;

use Domain\Service\DataTransferObjects\ServiceData;
use Domain\Service\Models\Service;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Support\Common\Actions\SyncMediaCollectionAction;
use Support\Common\DataTransferObjects\MediaCollectionData;
use Support\Common\DataTransferObjects\MediaData;
use Support\MetaData\Actions\CreateMetaDataAction;
use Support\MetaData\DataTransferObjects\MetaDataData;

class CreateServiceAction
{
    public function __construct(
        protected CreateMetaDataAction $createMetaData,
        protected SyncMediaCollectionAction $syncMediaCollection,
    ) {
    }

    public function execute(ServiceData $serviceData): Service|Model
    {
        $service = Service::create([
            'blueprint_id' => $serviceData->blueprint_id,
            'uuid' => (string) Str::uuid(),
            'name' => $serviceData->name,
            'description' => $serviceData->description,
            'retail_price' => $serviceData->retail_price,
            'selling_price' => $serviceData->selling_price,
            'billing_cycle' => $serviceData->billing_cycle,
            'due_date_every' => $serviceData->due_date_every,
            'is_featured' => $serviceData->is_featured,
            'is_special_offer' => $serviceData->is_special_offer,
            'pay_upfront' => $serviceData->pay_upfront,
            'is_subscription' => $serviceData->is_subscription,
            'status' => $serviceData->status,
            'needs_approval' => $serviceData->needs_approval,
            'is_auto_generated_bill' => $serviceData->is_auto_generated_bill,
        ]);

        $service->taxonomyTerms()->attach($serviceData->taxonomy_term_id);

        $this->createMetaData->execute($service, MetaDataData::fromArray($serviceData->meta_data ?? []));

        /** @var array<int, array> $mediaMaterials */
        $mediaMaterials = $serviceData->media_collection['materials'] ?? [];

        $media = collect($mediaMaterials)->map(function ($material): MediaData {
            /** @var UploadedFile|string $material */
            return new MediaData(media: $material);
        })->toArray();

        $this->syncMediaCollection->execute($service, new MediaCollectionData(
            collection: $serviceData->media_collection['collection'] ?? null,
            media: $media
        ));

        return $service;
    }
}
