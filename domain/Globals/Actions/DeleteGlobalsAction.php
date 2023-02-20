<?php

declare(strict_types=1);

namespace Domain\Globals\Actions;

use Domain\Globals\Models\Globals;

class DeleteGlobalsAction
{
    /** Execute a delete collection query. */
    public function execute(Globals $globals): ?bool
    {
        return $globals->delete();
    }
}
