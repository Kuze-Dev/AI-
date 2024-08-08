<?php

declare(strict_types=1);

namespace Domain\Blueprint\Actions;

use App\Settings\CustomerSettings;
use Domain\Blueprint\DataTransferObjects\BlueprintDataData;
use Domain\Blueprint\Enums\FieldType;
use Domain\Blueprint\Jobs\DeleteS3FilesFromDeletedBlueprintDataJob;
use Domain\Blueprint\Models\Blueprint;
use Domain\Blueprint\Models\BlueprintData;
use Domain\Content\Models\ContentEntry;
use Domain\Customer\Models\Customer;
use Domain\Globals\Models\Globals;
use Domain\Page\Models\BlockContent;
use Domain\Taxonomy\Models\TaxonomyTerm;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class UpdateBlueprintDataAction
{
    public function __construct(
        protected ExtractDataAction $extractDataAction,
        protected CreateBlueprintDataAction $createBlueprintData,
    ) {
    }

    public function execute(Model $model): void
    {
        $blueprintfieldtype = null;
        $blueprintDataArray = [];

        if ($model instanceof ContentEntry) {
            $blueprintfieldtype = $model->content->blueprint->schema;
        } elseif ($model instanceof BlockContent) {
            $blueprintfieldtype = $model->block->blueprint->schema;
        } elseif ($model instanceof Customer) {
            $blueprintfieldtype = Blueprint::where('id', app(CustomerSettings::class)->blueprint_id)->firstorfail()->schema;
        } elseif ($model instanceof TaxonomyTerm) {
            $blueprintfieldtype = $model->taxonomy->blueprint->schema;
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

        $this->sanitizeBlueprintStatePaths($flattenData, $model);

        foreach ($flattenData as $arrayData) {
            $blueprintDataArray[] = $this->updateBlueprintData($model, BlueprintDataData::fromArray($model, $arrayData));
        }

    }

    private function updateBlueprintData(Model $model, BlueprintDataData $blueprintDataData): BlueprintData
    {
        
        $blueprintData = BlueprintData::where('model_id', $blueprintDataData->model_id)
            ->where('model_type', $model->getMorphClass())
            ->where('state_path', $blueprintDataData->state_path)->first();

        if (! $blueprintData) {
            
            $this->createBlueprintData->execute($model);

            return $blueprintData = BlueprintData::where('model_id', $blueprintDataData->model_id)->where('state_path', $blueprintDataData->state_path)->first() ?: new BlueprintData();
        }

        if ($blueprintData->type == FieldType::MEDIA->value) {
            if (! $blueprintDataData->value) {
                return $blueprintData;
            }

            if (is_array($blueprintDataData->value)) {
                $toUpload = $blueprintDataData->value;
                $currentUploaded = $blueprintDataData->value;

                //filter array with value that has filename extension

                $filtered = array_filter($toUpload, function ($value) {
                    $pathInfo = pathinfo($value);
                    if (isset($pathInfo['extension']) && $pathInfo['extension'] !== '') {
                        return $value;
                    }
                });
                // filter $blueprintDataData->value array with value that has no filename extension

                $currentMedia = array_filter($currentUploaded, function ($value) {
                    $pathInfo = pathinfo($value);

                    if (! array_key_exists('extension', $pathInfo)) {
                        return $value;
                    }
                });

                foreach ($filtered as $image) {
                    if (Storage::disk(config('filament.default_filesystem_disk'))->exists($image)) {

                        $blueprintData->addMediaFromDisk($image, config('filament.default_filesystem_disk'))
                            ->toMediaCollection('blueprint_media');
                    }

                    $currentMedia[] = $blueprintData->getMedia('blueprint_media')->last()?->uuid;
                }

                $existingMedia = $blueprintData->getMedia('blueprint_media')->pluck('uuid')->toArray();

                $updatedMedia = array_intersect($existingMedia, $currentMedia);

                $exceptedMedia = Media::whereIN('uuid', $updatedMedia)->get();

                $blueprintData->clearMediaCollectionExcept('blueprint_media', $exceptedMedia);

                $blueprintData->update([
                    'model_id' => $blueprintDataData->model_id,
                    'value' => json_encode($updatedMedia),
                ]);
            }

        } else {

            $blueprintData->update([
                'model_id' => $blueprintDataData->model_id,
                'value' => $blueprintDataData->value,
            ]);
        }

        return $blueprintData;
    }

    private function sanitizeBlueprintStatePaths(array $flattenData, Model $model): void
    {

        $statepaths = array_column($flattenData, 'statepath');

        /** @phpstan-ignore-next-line */
        $removeBlueprintData = $model->blueprintData()->whereNotIn('state_path', $statepaths)->get();

        if ($removeBlueprintData->count()) {

            $toRemove = [];
            foreach ($removeBlueprintData as $itemtodelete) {

                $filtered = [];
                if (is_array($itemtodelete->value)) {
                    $filtered = array_filter($itemtodelete->value, function ($value) {
                        $pathInfo = pathinfo($value);
                        if (isset($pathInfo['extension']) && $pathInfo['extension'] !== '') {
                            return $value;
                        }
                    });

                }

                $toRemove = array_merge($toRemove, $filtered);

            }

            DeleteS3FilesFromDeletedBlueprintDataJob::dispatch(array_unique($toRemove));

            /** @phpstan-ignore-next-line */
            $model->blueprintData()->whereNotIn('state_path', $statepaths)->delete();

        }

    }
}
