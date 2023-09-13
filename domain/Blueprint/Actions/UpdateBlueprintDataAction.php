<?php

declare(strict_types=1);

namespace Domain\Blueprint\Actions;

use Domain\Blueprint\DataTransferObjects\BlueprintDataData;
use Domain\Blueprint\Enums\FieldType;
use Domain\Blueprint\Models\BlueprintData;
use Domain\Content\Models\ContentEntry;
use Domain\Page\Models\BlockContent;
use Illuminate\Database\Eloquent\Model;

class UpdateBlueprintDataAction
{
    public function __construct(
        protected ExtractDataAction $extractDataAction,
    ) {
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
            $this->updateBlueprintData(BlueprintDataData::fromArray($model, $statePath, $fieldType));
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
