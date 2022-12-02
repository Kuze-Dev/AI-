<?php 

declare (strict_types = 1);

namespace Domain\Collection\Actions;

use Domain\Collection\DataTransferObjects\CollectionData;
use Domain\Collection\Models\Collection;

class UpdateCollectionAction 
{
    /**
     * Execute operations for updating 
     * collection and save collection query.
     * 
     * @param Collection $collection
     * @param CollectionData $collectionData
     * 
     * @return Collection
     */
    public function execute(Collection $collection, CollectionData $collectionData): Collection
    {
        $collection->fill([
            'name' => $collectionData->name,
            'blueprint_id' => $collectionData->blueprint_id,
            'display_publish_dates' => $collectionData->display_publish_dates,
            'past_publish_date' => $collectionData->past_publish_date,
            'future_publish_date' => $collectionData->future_publsh_date
        ]);

        if ($collection->isDirty('blueprint_id')) {
            $collection->data = null;
        }

        $collection->save();

        return $collection;
    }
}