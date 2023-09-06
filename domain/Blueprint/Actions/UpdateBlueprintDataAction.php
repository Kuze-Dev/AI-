<?php

declare(strict_types=1);

namespace Domain\Blueprint\Actions;

use Domain\Blueprint\DataTransferObjects\BlueprintDataData;
use Domain\Blueprint\Enums\FieldType;
use Domain\Blueprint\Models\BlueprintData;
use Domain\Page\Models\BlockContent;

class UpdateBlueprintDataAction
{
    public function __construct(
        protected ExtractDataAction $extractDataAction,
    ) {
    }

    private function updateBlueprintData(BlueprintDataData $blueprintDataData): BlueprintData
    {
        $blueprintData = BlueprintData::where('model_id', $blueprintDataData->model_id)->where('state_path', $blueprintDataData->state_path)->first();
        $blueprintData->update([
            'model_id' => $blueprintDataData->model_id,
            'value' => $blueprintDataData->value,
        ]);

        if($blueprintData->type == FieldType::MEDIA) {
            $blueprintData->addMediaFromDisk($blueprintData->value, 's3')
                ->toMediaCollection('blueprint_media');
        }

        return $blueprintData;
    }

    public function execute(BlockContent $blockContent): BlueprintData
    {
        $blueprintfieldtype = $blockContent->block->blueprint->schema;
        $statePaths = $this->extractDataAction->extractStatePath($blockContent->data);
        $fieldTypes = $this->extractDataAction->extractFieldType($blueprintfieldtype, $statePaths);

        foreach (array_combine($statePaths, $fieldTypes) as $statePath => $fieldType) {

            $this->updateBlueprintData(BlueprintDataData::fromArray($blockContent, $statePath, $fieldType));
        }

        return new BlueprintData();
    }
}
