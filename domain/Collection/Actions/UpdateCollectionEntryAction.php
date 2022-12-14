<?php

declare(strict_types=1);

namespace Domain\Taxonomy\Actions;

use Domain\Collection\DataTransferObjects\CollectionEntryData;
use Domain\Collection\Models\CollectionEntry;

class UpdateCollectionEntryAction
{
    /**
     * Execute operations for updating
     * and save collection entry query.
     *
     * @param CollectionEntry $collectionEntry
     * @param CollectionEntryData $collectionEntryData
     *
     * @return CollectionEntry
     */
    public function execute(CollectionEntry $collectionEntry, CollectionEntryData $collectionEntryData): CollectionEntry
    {
        $collectionEntry->update(['data' => $collectionEntryData->data]);

        return $collectionEntry;
    }
}
