<?php

declare(strict_types=1);

namespace Domain\Blueprint\Actions;

use App\Settings\CustomerSettings;
use Domain\Blueprint\DataTransferObjects\BlueprintDataData;
use Domain\Blueprint\DataTransferObjects\ConversionData;
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
        protected GetFieldByStatePathAction $getFieldByStatePathAction,
    ) {}

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

        $newData = $this->getUpdateBlueprintData($model, $blueprintDataArray);

        $model->update(['data' => $newData]);

    }

    /**
     * @param  ContentEntry|BlockContent|Customer|TaxonomyTerm|Globals  $model
     * @param  BlueprintData[]  $blueprintDataArray  Array of BlueprintData models
     */
    public function getUpdateBlueprintData(Model $model, array $blueprintDataArray): array
    {
        $arrayData = $model->data;

        foreach ($blueprintDataArray as $decopuledData) {
            $statePath = $decopuledData->state_path;
            $newValue = $decopuledData->value;

            if ($decopuledData->type === FieldType::MEDIA->value) {

                // $newValue = $decopuledData->
                $newValue = $decopuledData->value
                    ? json_decode($decopuledData->value, true)
                    : [];
            }

            $keys = explode('.', $statePath);

            $temp = &$arrayData;

            // Traverse the array using the keys from the state path
            foreach ($keys as $key) {
                // If the key doesn't exist, create it as an array
                if (! isset($temp[$key])) {
                    $temp[$key] = [];
                }

                // Move deeper into the array
                $temp = &$temp[$key];
            }

            // Set the final key to the new value
            $temp = $newValue;
        }

        // Return the updated array
        return $arrayData;

    }

    public function updateBlueprintData(Model $model, BlueprintDataData $blueprintDataData): BlueprintData
    {

        $blueprintData = BlueprintData::where('model_id', $blueprintDataData->model_id)
            ->where('model_type', $model->getMorphClass())
            ->where('state_path', $blueprintDataData->state_path)->first();

        if (! $blueprintData) {

            $this->createBlueprintData->execute($model);

            return $blueprintData = BlueprintData::where('model_id', $blueprintDataData->model_id)->where('state_path', $blueprintDataData->state_path)->first() ?: new BlueprintData;
        }

        if ($blueprintData->type === FieldType::MEDIA->value) {
            /** @var \Domain\Blueprint\Models\Blueprint */
            $blueprint = Blueprint::where('id', $blueprintDataData->blueprint_id)->first();

            $formattedStatePath = collect(explode('.', $blueprintDataData->state_path))
                ->reject(fn ($segment) => is_numeric($segment))
                ->implode('.');

            /** @var \Domain\Blueprint\DataTransferObjects\MediaFieldData */
            $mediField = $this->getFieldByStatePathAction->execute($blueprint, $formattedStatePath);
            $conversions = $mediField->conversions;

            $savedConversions = array_map(
                fn (array $conversion) => ConversionData::fromArray($conversion),
                $blueprintData->blueprint_media_conversion ?? []
            );

            if (serialize($conversions) !== serialize($savedConversions) || is_null($blueprintData->blueprint_media_conversion)) {

                $blueprintData->update([
                    'blueprint_media_conversion' => $conversions,
                ]);

                $blueprintData->refresh();

                $mediaItems = $blueprintData->getMedia('blueprint_media');

                // regenerate conversions for media items
                foreach ($mediaItems as $mediaItem) {
                    app(\Support\Media\Actions\RegenerateImageConversions::class)->execute($mediaItem);
                }

            }

            if (! $blueprintDataData->value) {

                $blueprintData->clearMediaCollection('blueprint_media');

                $blueprintData->update([
                    'model_id' => $blueprintDataData->model_id,
                    'value' => $blueprintDataData->value,
                ]);

                return $blueprintData;
            }

            if (is_array($blueprintDataData->value)) {
                $toUpload = $blueprintDataData->value;
                $currentUploaded = $blueprintDataData->value;

                // filter array with value that has filename extension

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

                        $image = $blueprintData->addMediaFromDisk($image, config('filament.default_filesystem_disk'))
                            ->preservingOriginal()
                            ->toMediaCollection('blueprint_media');

                        $currentMedia[] = $image->uuid;
                    }
                }

                // media ordering..
                $blueprintData->refresh();

                $existingMedia = $blueprintData->getMedia('blueprint_media')->pluck('uuid')->toArray();

                $updatedMedia = array_intersect($currentMedia, $existingMedia);

                foreach ($currentMedia as $key => $media_uuid) {

                    if (in_array($media_uuid, $updatedMedia, true)) {

                        /** @var Media|null $media_item */
                        $media_item = Media::where('uuid', $media_uuid)->first();

                        if ($media_item) {
                            $media_item->order_column = $key + 1;
                            $media_item->save();
                        }
                    }

                }

                /**
                 *  $exceptedMedia = Media::whereIN('uuid', $updatedMedia)->get();
                 *  temporary disabled causing coversions to disappear only in production but working in local environment.
                 *
                 *  $blueprintData->clearMediaCollectionExcept('blueprint_media', $exceptedMedia);
                 ***/

                /**
                 *  handle manual deletion of remove medias
                 * **/
                $blueprintData->getMedia('blueprint_media')
                    ->whereNotIn('uuid', $updatedMedia)
                    ->each(fn ($media) => $media->delete());

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

        foreach ($flattenData as $item) {

            /** @phpstan-ignore-next-line */
            $blueprint_data_entity = $model->BlueprintData()->where('state_path', $item['statepath'])->get();

            if ($blueprint_data_entity->count() > 1) {
                $blueprint_entity_id = $blueprint_data_entity->pluck('id')->toArray();

                $minValue = min($blueprint_entity_id);

                $key = array_search($minValue, $blueprint_entity_id, true);

                unset($blueprint_entity_id[$key]);

                BlueprintData::whereIN('id', $blueprint_entity_id)->delete();
            }

        }

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
