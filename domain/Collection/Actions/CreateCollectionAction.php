<?php 

declare (strict_types = 1);

namespace Domain\Collection\Actions;

use Domain\Collection\DataTransferObjects\CollectionData;
use Domain\Collection\Models\Collection;

class CreateCollectionAction
{
    public function execute(CollectionData $collectionData): Collection
    {
        return Collection::create([
            'name' => $collectionData->name,
            'blueprint_id' => $collectionData->blueprint_id,
        ]);
    }
}