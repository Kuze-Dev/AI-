<?php

declare(strict_types=1);

namespace Domain\Blueprint\Actions;

use Domain\Blueprint\Database\Factories\BlueprintFactory;
use Domain\Blueprint\DataTransferObjects\BlueprintDataData;
use Domain\Blueprint\Models\BlueprintData;
use Domain\Page\Models\BlockContent;

class CreateBlueprintDataAction
{
    // private function storeBlueprintData(BlueprintDataData $blueprintDataData): BlueprintData
    // {
    //     return BlueprintData::create([
    //         'blueprint_id' => $blueprintDataData->blueprint_id,
    //         'model_id' => $blueprintDataData->model_id,
    //         'model_type' => $blueprintDataData->model_type,
    //         'state_path' => $blueprintDataData->state_path,
    //         'value' => $blueprintDataData->value,
    //         'type' => $blueprintDataData->type,
    //     ]);
    // }

    // public function execute(BlockContent $blockContent):BlueprintData
    // {
    //     $blueprintfieldtype = $blockContent->block->blueprint->schema;
    //     $data = json_decode($blockContent->data, true);

    //     $statePaths = $this->extractStatePaths($data);

    //     foreach ($statePaths as $statePath) {
    //       return $this->storeBlueprintData(BlueprintDataData::fromArray($blockContent, $statePath));
    //     }


    // }

    public function extractStatePathsAndFieldType(BlockContent $blockContent, $parentKey = ''): array
    {
        $state_path_data = [];
        $typeValues = [];

        foreach ($blockContent->block?->blueprint?->schema?->sections as $section) {
            foreach ($section->fields as $field) {
                if (isset($field->type)) {
                    $typeValues[] = $field->type->value;
                }
            }
        }

        foreach ($blockContent->data as $outerKey => $outerValue) {
            if (is_array($outerValue)) {
                foreach ($outerValue as $innerKey => $innerValue) {
                    $concatenatedKey = $outerKey . '.' . $innerKey;
                    $state_path_data[] = $concatenatedKey;
                }
            }
        }




        return [$state_path_data];
    }
}
