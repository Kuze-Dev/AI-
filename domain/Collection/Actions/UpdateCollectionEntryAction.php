<?php

declare(strict_types=1);

namespace Domain\Collection\Actions;

use Domain\Collection\DataTransferObjects\CollectionEntryData;
use Domain\Collection\Models\CollectionEntry;
use Domain\Support\MetaData\Actions\UpdateMetaDataAction;

class UpdateCollectionEntryAction
{
    public function __construct(
        protected UpdateMetaDataAction $updateMetaData
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
            'slug' => $collectionEntryData->slug,
            'published_at' => $collectionEntryData->published_at,
            'data' => $collectionEntryData->data,
        ]);

        $this->updateMetaData->execute($collectionEntry, $collectionEntryData->meta_data);

        $collectionEntry->taxonomyTerms()
            ->sync($collectionEntryData->taxonomy_terms);

        return $collectionEntry;
    }
}
