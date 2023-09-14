<?php

declare(strict_types=1);

namespace Domain\Blueprint\Actions;

use Domain\Blueprint\DataTransferObjects\BlueprintDataData;
use Domain\Blueprint\Enums\FieldType;
use Domain\Blueprint\Models\BlueprintData;
use Domain\Content\Models\ContentEntry;
use Domain\Page\Models\BlockContent;
use Illuminate\Database\Eloquent\Model;

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

        if($blueprintDataData->type == FieldType::MEDIA && $blueprintData->value) {

            if(is_array($blueprintDataData->value)) {
                foreach($blueprintDataData->value as $value) {
                    $blueprintData->addMediaFromDisk($value, 's3')
                        ->toMediaCollection('blueprint_media');
                }
            } else {

                $blueprintData->addMediaFromDisk($blueprintData->value, 's3')
                    ->toMediaCollection('blueprint_media');
            }

        }

        return $blueprintData;
    }

    public function execute(Model $model): void
    {
        $blueprintfieldtype = null;

        if ($model instanceof ContentEntry) {
            $blueprintfieldtype = $model->content->blueprint->schema;
        } elseif ($model instanceof BlockContent) {
            $blueprintfieldtype = $model->block->blueprint->schema;
        } else {
            return;
        }

        if( ! $model->data) {
            return;
        }

        $statePaths = $this->extractDataAction->extractStatePath($model->data);
        $fieldTypes = $this->extractDataAction->extractFieldType($blueprintfieldtype, $statePaths);
        foreach (array_combine($statePaths, $fieldTypes) as $statePath => $fieldType) {
            $this->storeBlueprintData(BlueprintDataData::fromArray($model, $statePath, $fieldType));
        }

    }
}
