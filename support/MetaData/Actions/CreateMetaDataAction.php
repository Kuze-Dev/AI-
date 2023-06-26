<?php

declare(strict_types=1);

namespace Support\MetaData\Actions;

use Support\Common\Actions\SyncMediaCollectionAction;
use Support\Common\DataTransferObjects\MediaCollectionData;
use Support\MetaData\Contracts\HasMetaData;
use Support\MetaData\DataTransferObjects\MetaDataData;
use Illuminate\Database\Eloquent\Model;

class CreateMetaDataAction
{
    public function __construct(
        protected SyncMediaCollectionAction $syncMediaCollectionAction
    ) {
    }

    public function execute(Model&HasMetaData $model, MetaDataData $metaDataData): Model
    {
        $defaults = $model->defaultMetaData();

        /** @var \Support\MetaData\Models\MetaData $metaData */
        $metaData = $model->metaData()
            ->create([
                'title' => $metaDataData->title ?? $defaults['title'] ?? null,
                'description' => $metaDataData->description ?? $defaults['description'] ?? null,
                'author' => $metaDataData->author ?? $defaults['author'] ?? null,
                'keywords' => $metaDataData->keywords ?? $defaults['keywords'] ?? null,
            ]);

        $this->syncMediaCollectionAction->execute(
            $metaData,
            MediaCollectionData::fromArray([
                'collection' => 'image',
                'media' => $metaDataData->image
                    ? [
                        'media' => $metaDataData->image,
                        'custom_properties' => ['alt_text' => $metaDataData->image_alt_text],
                    ]
                    : [],
            ])
        );

        return $model;
    }
}
