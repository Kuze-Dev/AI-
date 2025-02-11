<?php

declare(strict_types=1);

namespace Domain\Blueprint\Actions;

use App\Settings\CustomerSettings;
use Domain\Blueprint\DataTransferObjects\BlueprintDataData;
use Domain\Blueprint\Enums\FieldType;
use Domain\Blueprint\Models\Blueprint;
use Domain\Blueprint\Models\BlueprintData;
use Domain\Content\Models\ContentEntry;
use Domain\Customer\Models\Customer;
use Domain\Globals\Models\Globals;
use Domain\Page\Models\BlockContent;
use Domain\Taxonomy\Models\TaxonomyTerm;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class CreateBlueprintDataAction
{
    public function __construct(
        protected ExtractDataAction $extractDataAction,
    ) {
    }

    public function storeBlueprintData(BlueprintDataData $blueprintDataData): BlueprintData
    {

        $blueprintData = BlueprintData::create([
            'blueprint_id' => $blueprintDataData->blueprint_id,
            'model_id' => $blueprintDataData->model_id,
            'model_type' => $blueprintDataData->model_type,
            'state_path' => $blueprintDataData->state_path,
            'value' => $blueprintDataData->value,
            'type' => $blueprintDataData->type,
        ]);

        if ($blueprintDataData->type == FieldType::LOCATION_PICKER && $blueprintData->value) {
            return $blueprintData;
        }

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
                    if (Storage::disk(config('filament.default_filesystem_disk'))->exists($value)) {
                        $blueprintData->addMediaFromDisk($value, config('filament.default_filesystem_disk'))
                            ->toMediaCollection('blueprint_media');
                    }
                }
            } else {
                $blueprintData->addMediaFromDisk($blueprintData->value, config('filament.default_filesystem_disk'))
                    ->toMediaCollection('blueprint_media');
            }

            $existingMedia = $blueprintData->getMedia('blueprint_media')->pluck('uuid')->toArray();

            $blueprintData->update([
                'model_id' => $blueprintDataData->model_id,
                'value' => json_encode($existingMedia),
            ]);

            $blueprintData->refresh();
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
        } elseif ($model instanceof TaxonomyTerm) {
            $blueprintfieldtype = $model->taxonomy->blueprint->schema;
        } elseif ($model instanceof Customer) {
            $blueprintfieldtype = Blueprint::where('id', app(CustomerSettings::class)->blueprint_id)->firstorfail()->schema;
        } elseif ($model instanceof Globals) {
            $blueprintfieldtype = $model->blueprint->schema;
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
