<?php

declare(strict_types=1);

namespace Domain\Blueprint\Actions;

use Domain\Blueprint\DataTransferObjects\BlueprintDataData;
use Domain\Blueprint\Enums\FieldType;
use Domain\Blueprint\Models\BlueprintData;
use Domain\Page\Models\BlockContent;

class CreateBlueprintDataAction
{
    public function __construct(
        protected ExtractDataAction $extractDataAction,
    ) {
    }

    private function storeBlueprintData(BlueprintDataData $blueprintDataData): BlueprintData
    {

        $blueprintData = BlueprintData::create([
            'blueprint_id' => $blueprintDataData->blueprint_id,
            'model_id' => $blueprintDataData->model_id,
            'model_type' => $blueprintDataData->model_type,
            'state_path' => $blueprintDataData->state_path,
            'value' => $blueprintDataData->value,
            'type' => $blueprintDataData->type,
        ]);

        if($blueprintData->type == FieldType::MEDIA) {
            $blueprintData->addMediaFromDisk($blueprintData->value, 's3')
                ->toMediaCollection('blueprint_media');
        }

        return $blueprintData;
    }

    public function execute(BlockContent $blockContent): void
    {
        $blueprintfieldtype = $blockContent->block->blueprint->schema;
        $statePaths = $this->extractDataAction->extractStatePath($blockContent->data);
        $fieldTypes = $this->extractDataAction->extractFieldType($blueprintfieldtype, $statePaths);
        foreach (array_combine($statePaths, $fieldTypes) as $statePath => $fieldType) {
            $this->storeBlueprintData(BlueprintDataData::fromArray($blockContent, $statePath, $fieldType));
        }

    }
}
