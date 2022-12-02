<?php

declare (strict_types = 1);

namespace Domain\Collection\Actions;

use Domain\Collection\DataTransferObjects\CollectionEntryData;
use Domain\Collection\Models\CollectionEntry;

class CreateCollectionEntryAction
{
    /**
     * Execute create collection query.
     *  
     * @param CollectionData $collectionData
     * 
     * @return Collection
     */
    public function execute(CollectionEntryData $collectionEntryData): CollectionEntry
    {  
        return CollectionEntry::create([
            'collection_id' => $collectionEntryData->collection_id,
            'data' => $collectionEntryData->data
        ]);
    }
}