<?php

declare(strict_types=1);

namespace Domain\Globals\Actions;

use Domain\Collection\Models\Collection;

class DeleteGlobalsAction
{
    /** Execute a delete collection query. */
    public function execute(Collection $collection): ?bool
    {
        return $collection->delete();
    }
}
