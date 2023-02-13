<?php

declare(strict_types=1);

namespace Domain\Collection\Actions;

use Domain\Collection\DataTransferObjects\CollectionEntryData;
use Domain\Collection\Models\CollectionEntry;
use Domain\Support\MetaTag\Actions\UpdateMetaTagsAction;

class UpdateCollectionEntryAction
{
    public function __construct(
        protected UpdateMetaTagsAction $updateMetaTags
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

        $this->updateMetaTags->execute($collectionEntry, $collectionEntryData->meta_tags);

        $collectionEntry->taxonomyTerms()
            ->sync($collectionEntryData->taxonomy_terms);

        return $collectionEntry;
    }
}
