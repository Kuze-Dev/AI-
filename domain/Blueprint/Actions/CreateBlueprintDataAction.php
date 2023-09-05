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

        if($blueprintData->type === FieldType::MEDIA) {
            $blueprintData->addMediaFromDisk($blueprintData->value, 's3')
                ->toMediaCollection('blueprint_media');
        }

        return new BlueprintData();
    }

    public function execute(BlockContent $blockContent): BlueprintData
    {

        $sections = $blockContent->block->blueprint->schema->sections;
        $stateNames = $this->extractStateNames($sections);
        $blockContentArray = $blockContent->data;

        $rearrangedArray = [];

        foreach ($blockContentArray as $key => $blockContentinnerArray) {
            if (is_array($blockContentinnerArray)) {
                $rearrangedArray[$key] = $this->rearrangeBlockContentInnerArray($blockContentinnerArray, $stateNames);
            }
        }

        $blueprintfieldtype = $blockContent->block->blueprint->schema;
        $statePaths = $this->extractDataAction->extractStatePath($rearrangedArray);
        $fieldTypes = $this->extractDataAction->extractFieldType($blueprintfieldtype);
        foreach (array_combine($statePaths, $fieldTypes) as $statePath => $fieldType) {
            $this->storeBlueprintData(BlueprintDataData::fromArray($blockContent, $statePath, $fieldType));
        }

        return new BlueprintData();
    }

    // Function to extract state names from sections and fields
    private function extractStateNames(array $sections)
    {
        $stateNames = [];
        foreach ($sections as $section) {
            foreach ($section->fields as $field) {
                $stateNames[] = $field->state_name;
            }
        }

        return $stateNames;
    }

    // Function to rearrange the inner array based on state names
    private function rearrangeBlockContentInnerArray(array $innerArray, array $stateNames)
    {
        $rearrangedBlockContentInnerArray = [];
        foreach ($stateNames as $stateName) {
            if (array_key_exists($stateName, $innerArray)) {
                $rearrangedBlockContentInnerArray[$stateName] = $innerArray[$stateName];
            }
        }

        return $rearrangedBlockContentInnerArray;
    }
}
