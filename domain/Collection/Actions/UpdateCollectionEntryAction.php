<?php

declare(strict_types=1);

namespace Domain\Collection\Actions;

use Domain\Collection\DataTransferObjects\CollectionEntryData;
use Domain\Collection\Models\CollectionEntry;
use Domain\Support\MetaData\Actions\CreateMetaDataAction;
use Domain\Support\MetaData\Actions\UpdateMetaDataAction;
use Domain\Support\RouteUrl\Actions\CreateOrUpdateRouteUrlAction;

class UpdateCollectionEntryAction
{
    public function __construct(
        protected CreateMetaDataAction $createMetaData,
        protected UpdateMetaDataAction $updateMetaData,
        protected CreateOrUpdateRouteUrlAction $createOrUpdateRouteUrl,
    ) {
    }

    /**
     * Execute operations for updating
     * and save collection entry query.
     */
    public function execute(CollectionEntry $collectionEntry, CollectionEntryData $collectionEntryData): CollectionEntry
    {
        $collectionEntry->update([
            'title' => $collectionEntryData->title,
            'published_at' => $collectionEntryData->published_at,
            'data' => $collectionEntryData->data,
        ]);

        $collectionEntry->metaData()->exists()
            ? $this->updateMetaData->execute($collectionEntry, $collectionEntryData->meta_data)
            : $this->createMetaData->execute($collectionEntry, $collectionEntryData->meta_data);

        $collectionEntry->taxonomyTerms()
            ->sync($collectionEntryData->taxonomy_terms);

        $this->createOrUpdateRouteUrl->execute($collectionEntry, $collectionEntryData->route_url_data);

        return $collectionEntry;
    }
}
