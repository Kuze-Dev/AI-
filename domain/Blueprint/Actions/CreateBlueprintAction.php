<?php

declare(strict_types=1);

namespace Domain\Blueprint\Actions;

use Domain\Blueprint\DataTransferObjects\BlueprintData;
use Domain\Blueprint\Models\Blueprint;

class CreateBlueprintAction
{
    public function execute(BlueprintData $blueprintData): Blueprint
    {
        return Blueprint::create([
            'id' => $blueprintData->id,
            'name' => $blueprintData->name,
            'schema' => $blueprintData->schema,
        ]);
    }
}
