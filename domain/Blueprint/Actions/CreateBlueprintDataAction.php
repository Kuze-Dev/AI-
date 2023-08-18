<?php

declare(strict_types=1);

namespace Domain\Blueprint\Actions;

use Domain\Blueprint\DataTransferObjects\BlueprintDataData;
use Domain\Blueprint\Models\BlueprintData;

class CreateBlueprintDataAction
{
    public function execute(BlueprintDataData $blueprintDataData): BlueprintData
    {
        return BlueprintData::create([
            'blueprint_id' => $blueprintDataData->blueprint_id,
            'model_id' => $blueprintDataData->model_id,
            'model_type' => $blueprintDataData->model_type,
            'state_path' => $blueprintDataData->state_path,
            'value' => $blueprintDataData->value,
            'type' => $blueprintDataData->type,
        ]);
    }
}
