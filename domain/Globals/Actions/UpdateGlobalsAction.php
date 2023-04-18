<?php

declare(strict_types=1);

namespace Domain\Globals\Actions;

use Domain\Globals\DataTransferObjects\GlobalsData;
use Domain\Globals\Models\Globals;

class UpdateGlobalsAction
{
    /**
     * Execute operations for updating
     * collection and save collection query.
     */
    public function execute(Globals $globals, GlobalsData $globalData): Globals
    {
        $globals->update([
            'name' => $globalData->name,
            'data' => $globalData->data,
        ]);

        return $globals;
    }
}
