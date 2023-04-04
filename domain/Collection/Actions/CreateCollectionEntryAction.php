<?php

declare(strict_types=1);

namespace Domain\Collection\Actions;

use Domain\Collection\DataTransferObjects\CollectionEntryData;
use Domain\Collection\Models\Collection;
use Domain\Collection\Models\CollectionEntry;
use Domain\Support\MetaData\Actions\CreateMetaDataAction;
use Domain\Support\RouteUrl\Actions\UpdateOrCreateRouteUrlAction;

class CreateCollectionEntryAction
{
    public function __construct(
        protected CreateMetaDataAction $createMetaData,
        protected UpdateOrCreateRouteUrlAction $createRouteUrl,
    ) {
    }

    /** Execute create collection entry query. */
    public function execute(Collection $collection, CollectionEntryData $collectionEntryData): CollectionEntry
    {
        /** @var CollectionEntry $collectionEntry */
        $collectionEntry = $collection->collectionEntries()
            ->create([
                'title' => $collectionEntryData->title,
                'data' => $collectionEntryData->data,
                'published_at' => $collectionEntryData->published_at,
            ]);

        $this->createMetaData->execute($collectionEntry, $collectionEntryData->meta_data);

        $collectionEntry->taxonomyTerms()
            ->attach($collectionEntryData->taxonomy_terms);

        $this->createRouteUrl->execute($collectionEntry, $collectionEntryData->url_data);

        return $collectionEntry;
    }
}
