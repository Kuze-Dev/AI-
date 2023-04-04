<?php

declare(strict_types=1);

namespace Domain\Collection\Actions;

use Domain\Collection\DataTransferObjects\CollectionData;
use Domain\Collection\Models\Collection;
use Domain\Support\RouteUrl\Actions\UpdateOrCreateRouteUrlAction;

class UpdateCollectionAction
{
    public function __construct(
        protected UpdateOrCreateRouteUrlAction $updateOrCreateRouteUrl,
    ) {
    }

    /**
     * Execute operations for updating
     * collection and save collection query.
     */
    public function execute(Collection $collection, CollectionData $collectionData): Collection
    {
        $collection->update([
            'name' => $collectionData->name,
            'past_publish_date_behavior' => $collectionData->past_publish_date_behavior,
            'future_publish_date_behavior' => $collectionData->future_publish_date_behavior,
            'is_sortable' => $collectionData->is_sortable,
        ]);

        $collection->taxonomies()
            ->sync($collectionData->taxonomies);

        $this->updateOrCreateRouteUrl->execute($collection, $collectionData->url_data);

        return $collection;
    }
}
