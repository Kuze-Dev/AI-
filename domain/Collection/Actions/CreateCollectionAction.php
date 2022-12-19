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
        return Collection::create([
            'name' => $collectionData->name,
            'slug' => $collectionData->slug,
            'blueprint_id' => $collectionData->blueprint_id,
            'past_publish_date' => $collectionData->past_publish_date,
            'future_publish_date' => $collectionData->future_publish_date,
            'is_sortable' => $collectionData->is_sortable,
        ]);
    }
}
