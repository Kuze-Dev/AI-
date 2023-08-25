<?php

declare(strict_types=1);

namespace Domain\Blueprint\Actions;

use Domain\Blueprint\DataTransferObjects\BlueprintDataData;
use Domain\Blueprint\DataTransferObjects\SchemaData;
use Domain\Blueprint\Enums\FieldType;
use Domain\Blueprint\Models\BlueprintData;
use Domain\Page\Models\BlockContent;

class UpdateBlueprintDataAction
{
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

        return new BlueprintData();
    }

    public function execute(BlockContent $blockContent): BlueprintData
    {
        $blueprintfieldtype = $blockContent->block->blueprint->schema;
        $statePaths = $this->extractStatePath($blockContent->data);
        $fieldTypes = $this->extractFieldType($blueprintfieldtype);

        foreach (array_combine($statePaths, $fieldTypes) as $statePath => $fieldType) {

            $this->updateBlueprintData(BlueprintDataData::fromArray($blockContent, $statePath, $fieldType));
        }

        return new BlueprintData();
    }

    private function extractStatePath(array $data, $parentKey = ''): array
    {
        $statePaths = [];

        foreach ($data as $key => $value) {
            $currentPath = ($parentKey !== '') ? $parentKey . '.' . $key : $key;
            if (is_array($value)) {
                $nestedPaths = $this->extractStatePath($value, $currentPath);
                $statePaths = array_merge($statePaths, $nestedPaths);
            } else {
                $statePaths[] = $currentPath;
            }
        }

        return $statePaths;
    }

    private function extractFieldType(SchemaData $blueprintfieldtype, $parentKey = ''): array
    {
        $fieldTypes = [];

        foreach ($blueprintfieldtype->sections as $section) {
            foreach ($section->fields as $field) {
                if (isset($field->type)) {
                    $fieldTypes[] = $field->type;
                }
            }
        }

        return $fieldTypes;
    }
}
