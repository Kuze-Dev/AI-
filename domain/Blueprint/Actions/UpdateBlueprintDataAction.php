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

    public function execute(BlockContent $blockContent): void
    {
        $blueprintfieldtype = $blockContent->block->blueprint->schema;
        if( ! $blockContent->data) {
            return;
        }
        $statePaths = $this->extractDataAction->extractStatePath($blockContent->data);
        $fieldTypes = $this->extractDataAction->extractFieldType($blueprintfieldtype, $statePaths);
        foreach (array_combine($statePaths, $fieldTypes) as $statePath => $fieldType) {
            $this->updateBlueprintData(BlueprintDataData::fromArray($blockContent, $statePath, $fieldType));
        }

    }

    private function updateBlueprintData(BlueprintDataData $blueprintDataData): BlueprintData
    {
        $blueprintData = BlueprintData::where('model_id', $blueprintDataData->model_id)->where('state_path', $blueprintDataData->state_path)->first();
        if( ! $blueprintData) {
            return new BlueprintData();
        }
        if ($blueprintData->type == FieldType::MEDIA->value) {
            if( ! $blueprintDataData->value) {
                return $blueprintData;
            }
            $pathInfo = pathinfo($blueprintDataData->value);

            if (isset($pathInfo['extension']) && $pathInfo['extension'] !== '') {
                $blueprintData->update([
                    'model_id' => $blueprintDataData->model_id,
                    'value' => $blueprintDataData->value,
                ]);

                $blueprintData->clearMediaCollection('blueprint_media');
                if($blueprintData->value) {
                    $blueprintData->addMediaFromDisk($blueprintData->value, 's3')
                        ->toMediaCollection('blueprint_media');
                }
            } else {
            }
        } else {
            $blueprintData->update([
                'model_id' => $blueprintDataData->model_id,
                'value' => $blueprintDataData->value,
            ]);
        }

        return $blueprintData;
    }
}
