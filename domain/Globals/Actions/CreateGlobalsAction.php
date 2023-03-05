<?php

declare(strict_types=1);

namespace Domain\Globals\Actions;

use Domain\Globals\DataTransferObjects\GlobalsData;
use Domain\Globals\Models\Globals;

class CreateGlobalsAction
{
    /** Execute create collection query. */
    public function execute(GlobalsData $globalData): Globals
    {
        $globals = Globals::create([
            'name' => $globalData->name,
            'slug' => $globalData->slug,
            'blueprint_id' => $globalData->blueprint_id,
            'data' => $globalData->data,
        ]);

        $globals->sites()
            ->attach($globalData->sites);

        return $globals;
    }
}
