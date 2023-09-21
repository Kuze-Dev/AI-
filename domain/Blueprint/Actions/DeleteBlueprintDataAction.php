<?php

declare(strict_types=1);

namespace Domain\Blueprint\Actions;

use Domain\Blueprint\Models\BlueprintData;

class DeleteBlueprintDataAction
{
    public function execute(BlueprintData $blueprintData): ?bool
    {
        return $blueprintData->delete();
    }
}
