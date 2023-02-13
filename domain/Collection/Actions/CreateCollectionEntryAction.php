<?php

declare(strict_types=1);

namespace Domain\Collection\Actions;

use Domain\Collection\DataTransferObjects\CollectionEntryData;
use Domain\Collection\Models\Collection;
use Domain\Collection\Models\CollectionEntry;
use Domain\Support\MetaTag\Actions\CreateMetaTagsAction;

class CreateCollectionEntryAction
{
    public function __construct(
        protected CreateMetaTagsAction $createMetaTags
    ) {
    }

    /** Execute create collection entry query. */
    public function execute(Collection $collection, CollectionEntryData $collectionEntryData): CollectionEntry
    {
        $collectionEntry = $collection->collectionEntries()
            ->create([
                'title' => $collectionEntryData->title,
                'slug' => $collectionEntryData->slug,
                'data' => $collectionEntryData->data,
                'published_at' => $collectionEntryData->published_at,
            ]);

        $this->createMetaTags->execute($collectionEntry, $collectionEntryData->meta_tags);

        $collectionEntry->taxonomyTerms()
            ->attach($collectionEntryData->taxonomy_terms);

        return $collectionEntry;
    }
}
