<?php

declare(strict_types=1);

namespace Domain\Blueprint\Actions;

use Domain\Blueprint\DataTransferObjects\BlueprintDataData;
use Domain\Blueprint\DataTransferObjects\SchemaData;
use Domain\Blueprint\Models\BlueprintData;
use Domain\Page\Models\BlockContent;

class CreateBlueprintDataAction
{
    private function storeBlueprintData(BlueprintDataData $blueprintDataData): BlueprintData
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

    public function execute(BlockContent $blockContent): BlueprintData
    {
        $blueprintfieldtype = $blockContent->block->blueprint->schema;
        $statePaths = $this->extractStatePath($blockContent->data);
        $fieldTypes = $this->extractFieldType($blueprintfieldtype);

        for ($i = 0; $i < count($statePaths); $i++) {
            $statePath = $statePaths[$i];
            $fieldType = $fieldTypes[$i];

            $this->storeBlueprintData(BlueprintDataData::fromArray($blockContent, $statePath, $fieldType));
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
