<?php

declare(strict_types=1);

namespace Domain\Blueprint\Actions;

use Domain\Blueprint\Database\Factories\BlueprintFactory;
use Domain\Blueprint\DataTransferObjects\BlueprintDataData;
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

    public function execute(BlockContent $blockContent):BlueprintData
    {
        $blueprintfieldtype = $blockContent->block->blueprint->schema;
        $data = json_decode($blockContent->data, true);

        $statePaths = $this->extractStatePaths($data);

        foreach ($statePaths as $statePath) {
          return $this->storeBlueprintData(BlueprintDataData::fromArray($blockContent, $statePath));
        }


    }

    private function extractStatePathsAndFieldType(BlockContent $blockContent, $parentKey = ''): array
    {
        $state_path_data = json_decode($blockContent->data, true);
        $field_type_data = $blockContent->block?->blueprint?->schema;

        $statePaths = [];
        foreach ($state_path_data as $key => $value) {
            $currentKeyPath = ($parentKey !== '') ? "$parentKey.$key" : $key;

            if (is_array($value) || is_object($value)) {
                $nestedPaths = $this->extractStatePathsAndFieldType($value, $currentKeyPath);
                $statePaths = array_merge($statePaths, $nestedPaths);
            } else {
                $statePaths[] = $currentKeyPath;
            }
        }

        $typeValues = [];

        foreach ($field_type_data as $key => $value) {
            if ($key === "type") {
                $typeValues[] = $value;
            } elseif (is_array($value) || is_object($value)) {
                $nestedTypeValues = $this->extractStatePathsAndFieldType($value);
                $typeValues = array_merge($typeValues, $nestedTypeValues);
            }
        }


        return $statePaths;
    }
}
