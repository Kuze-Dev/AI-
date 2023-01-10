<?php

declare(strict_types=1);

namespace Domain\Collection\Actions;

use Domain\Collection\DataTransferObjects\CollectionData;
use Domain\Collection\Models\Collection;

class CreateCollectionAction
{
    /** Execute create collection query. */
    public function execute(CollectionData $collectionData): Collection
    {
        $collection = Collection::create([
            'name' => $collectionData->name,
            'slug' => $collectionData->slug,
            'blueprint_id' => $collectionData->blueprint_id,
            'past_publish_date_behavior' => $collectionData->past_publish_date_behavior,
            'future_publish_date_behavior' => $collectionData->future_publish_date_behavior,
            'is_sortable' => $collectionData->is_sortable,
        ]);

        if (!empty($collectionData->taxonomies)) {
            $collection->taxonomies()->attach($collectionData->taxonomies);
        }

        return $collection;
    }
}
