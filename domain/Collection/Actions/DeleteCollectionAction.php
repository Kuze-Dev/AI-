<?php 

declare (strict_types = 1);

namespace Domain\Collection\Actions;

use Domain\Collection\Models\Collection;

class DeleteCollectionAction
{
    /**
     * Execute a delete collection query.
     */
    public function execute (Collection $collection): ?bool 
    {
        return $collection->delete();
    }
}