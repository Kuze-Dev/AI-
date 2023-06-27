<?php

declare(strict_types=1);

namespace Domain\Support\MetaData\Actions;

use Domain\Support\Common\Actions\SyncMediaCollectionAction;
use Domain\Support\Common\DataTransferObjects\MediaCollectionData;
use Domain\Support\MetaData\Contracts\HasMetaData;
use Domain\Support\MetaData\DataTransferObjects\MetaDataData;
use Illuminate\Database\Eloquent\Model;

class UpdateMetaDataAction
{
    public function __construct(
        protected SyncMediaCollectionAction $syncMediaCollectionAction
    ) {
    }

    public function execute(Model&HasMetaData $model, MetaDataData $metaDataData): Model
    {
        $defaults = $model->defaultMetaData();

        /** @var \Domain\Support\MetaData\Models\MetaData */
        $metaData = $model->metaData()->first();

        $metaData->update([
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
