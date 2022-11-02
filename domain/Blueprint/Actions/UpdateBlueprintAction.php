<?php

declare(strict_types=1);

namespace Domain\Blueprint\Actions;

use Domain\Blueprint\DataTransferObjects\BlueprintData;
use Domain\Blueprint\Models\Blueprint;

class UpdateBlueprintAction
{
    public function execute(Blueprint $blueprint, BlueprintData $blueprintData): Blueprint
    {
        $blueprint->update([
            'name' => $blueprintData->name,
            'schema' => $blueprintData->schema,
        ]);

        return $blueprint;
    }
}
