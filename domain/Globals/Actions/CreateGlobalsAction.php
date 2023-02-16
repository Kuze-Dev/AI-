<?php

declare(strict_types=1);

namespace Domain\Globals\Actions;

use Domain\Globals\DataTransferObjects\GlobalsData;
use Domain\Globals\Models\Globals;

class CreateGlobalsAction
{
    /** Execute create collection query. */
    public function execute(GlobalsData $globalData): Collection
    {
          dd($globalData);
    }
}
