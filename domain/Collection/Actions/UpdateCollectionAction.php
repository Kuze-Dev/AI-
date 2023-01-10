<?php

declare(strict_types=1);

namespace Domain\Collection\Actions;

use Domain\Collection\DataTransferObjects\CollectionData;
use Domain\Collection\Models\Collection;

class UpdateCollectionAction
{
    /**
     * Execute operations for updating
     * collection and save collection query.
     */
    public function execute(Collection $collection, CollectionData $collectionData): Collection
    {
        $collection->update([
            'name' => $collectionData->name,
            'blueprint_id' => $collectionData->blueprint_id,
            'past_publish_date_behavior' => $collectionData->past_publish_date_behavior,
            'future_publish_date_behavior' => $collectionData->future_publish_date_behavior,
            'is_sortable' => $collectionData->is_sortable,
        ]);

        $collection->taxonomies()->sync($collectionData->taxonomies);

        return $collection;
    }
}
