<?php

declare(strict_types=1);

namespace Domain\Collection\Actions;

use Domain\Collection\DataTransferObjects\CollectionEntryData;
use Domain\Collection\Models\Collection;
use Domain\Collection\Models\CollectionEntry;

class CreateCollectionEntryAction
{
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

        if (!empty($collectionEntryData->taxonomy_terms)) {
            $collectionEntry->taxonomyTerms()
                ->attach($collectionEntryData->taxonomy_terms);
        }
        
        return $collectionEntry;
    }
}
