<?php

declare(strict_types=1);

namespace Domain\Collection\Actions;

use Domain\Collection\DataTransferObjects\CollectionData;
use Domain\Collection\Models\Collection;
use Domain\Support\RouteUrl\Actions\CreateOrUpdateRouteUrlAction;

class CreateCollectionAction
{
    public function __construct(
        protected CreateOrUpdateRouteUrlAction $createOrUpdateRouteUrl,
    ) {
    }

    /** Execute create collection query. */
    public function execute(CollectionData $collectionData): Collection
    {
        $collection = Collection::create([
            'name' => $collectionData->name,
            'blueprint_id' => $collectionData->blueprint_id,
            'past_publish_date_behavior' => $collectionData->past_publish_date_behavior,
            'future_publish_date_behavior' => $collectionData->future_publish_date_behavior,
            'is_sortable' => $collectionData->is_sortable,
        ]);

        $collection->taxonomies()
            ->attach($collectionData->taxonomies);

        $this->createOrUpdateRouteUrl->execute($collection, $collectionData->route_url_data);

        return $collection;
    }
}
