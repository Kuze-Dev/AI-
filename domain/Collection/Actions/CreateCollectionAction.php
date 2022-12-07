<?php 

declare (strict_types = 1);

namespace Domain\Collection\Actions;

use Domain\Collection\DataTransferObjects\CollectionData;
use Domain\Collection\Models\Collection;

class CreateCollectionAction
{
    /**
     * Execute create collection query.
     *  
     * @param CollectionData $collectionData
     * 
     * @return Collection
     */
    public function execute(CollectionData $collectionData): Collection
    {
        return Collection::create([
            'name' => $collectionData->name,
            'slug' => $collectionData->slug,
            'blueprint_id' => $collectionData->blueprint_id,
            'display_publish_dates' => $collectionData->display_publish_dates,
            'past_publish_date' => $collectionData->past_publish_date,
            'future_publish_date' => $collectionData->future_publsh_date,
            'isSortable' => $collectionData->isSortable
        ]);
    }
}