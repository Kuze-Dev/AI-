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

        if (is_array($blueprintDataData->value)) {
            $filtered = array_filter($blueprintDataData->value, function ($value) {
                $pathInfo = pathinfo($value);
                if (isset($pathInfo['extension']) && $pathInfo['extension'] !== '') {
                    return $value;
                }
            });
            if (empty($filtered)) {
                return $blueprintData;
            }
        }

        if ($blueprintDataData->type == FieldType::MEDIA && $blueprintData->value) {
            if (is_array($blueprintDataData->value)) {
                foreach ($blueprintDataData->value as $value) {
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

        if (! $model->data) {
            return;
        }

        $extractedDatas = $this->extractDataAction->extractStatePathAndFieldTypes($blueprintfieldtype->sections);

        $combinedArray = [];
        $data = [];
        foreach ($extractedDatas as $sectionKey => $sectionValue) {
            foreach ($sectionValue as $fieldKey => $fieldValue) {
                $combinedArray[$sectionKey][$fieldKey] = $this->extractDataAction->mergeFields($fieldValue, $model->data[$sectionKey][$fieldKey], $fieldValue['statepath']);
            }
        }
        foreach ($combinedArray as $section) {
            foreach ($section as $field) {
                $data[] = $this->extractDataAction->processRepeaterField($field);
            }
        }
        $flattenData = $this->extractDataAction->flattenArray($data);

        foreach ($flattenData as $arrayData) {
            $this->storeBlueprintData(BlueprintDataData::fromArray($model, $arrayData));
        }
    }
}
