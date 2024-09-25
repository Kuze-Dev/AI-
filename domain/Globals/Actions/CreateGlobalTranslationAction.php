<?php

declare(strict_types=1);

namespace Domain\Globals\Actions;

use Domain\Blueprint\Actions\CreateBlueprintDataAction;
use Domain\Blueprint\Actions\ExtractDataAction;
use Domain\Blueprint\Actions\UpdateBlueprintDataAction;
use Domain\Blueprint\DataTransferObjects\BlueprintDataData;
use Domain\Content\Models\Content;
use Domain\Globals\DataTransferObjects\GlobalsData;
use Domain\Globals\Models\Globals;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Support\MetaData\Actions\CreateMetaDataAction;
use Support\RouteUrl\Actions\CreateOrUpdateRouteUrlAction;

class CreateGlobalTranslationAction
{
    public function __construct(
        protected CreateMetaDataAction $createMetaData,
        protected CreateOrUpdateRouteUrlAction $createOrUpdateRouteUrl,
        protected CreateBlueprintDataAction $createBlueprintDataAction,
        protected UpdateBlueprintDataAction $updateBlueprintDataAction,
    ) {
    }

    /** Execute create content entry query. */
    public function execute(Globals $global, GlobalsData $globalData): Globals
    {
        /** @var Globals $globalTranslation */
        $globalTranslation = $global->globalsTranslation()
            ->create([
                'name' => $globalData->name,
                'blueprint_id' => $globalData->blueprint_id,
                'locale' => $globalData->locale,
                'data' => $globalData->data,
            ]);

        $this->createBlueprintDataAction->execute($globalTranslation);

        if (tenancy()->tenant?->features()->active(\App\Features\CMS\SitesManagement::class)) {

            $globalTranslation->sites()->sync($globalData->sites);
        }

        $this->handleGlobalTranslation($global, $globalTranslation);

        $globalTranslation->refresh();

        return $globalTranslation;

    }

    private function handleGlobalTranslation(Globals $global, Globals $translatedGlobals): void
    {
        $extractedDatas = app(ExtractDataAction::class)->extractStatePathAndFieldTypes($global->blueprint->schema->sections);

        /** @var array */
        $combinedArray = [];

        $data = [];

        foreach ($extractedDatas as $sectionKey => $sectionValue) {
            foreach ($sectionValue as $fieldKey => $fieldValue) {
                $combinedArray[$sectionKey][$fieldKey] = app(ExtractDataAction::class)->mergeFields($fieldValue, $global->data[$sectionKey][$fieldKey], $fieldValue['statepath']);
            }
        }

        foreach ($combinedArray as $section) {
            foreach ($section as $field) {
                $data[] = app(ExtractDataAction::class)->processRepeaterField($field);
            }
        }

        $flattenData = app(ExtractDataAction::class)->flattenArray($data);

        $filtered = array_filter($flattenData, function ($item) {
            return isset($item['translatable']) && $item['translatable'] === false;
        });

        if (
            count($filtered) > 0
        ) {
            $data = $this->updateJsonByStatePaths($translatedGlobals, $filtered);

            $translatedGlobals->update([
                'data' => $data,
            ]);

        }
    }

    private function updateJsonByStatePaths(Globals $item, array $updates): array
    {

        $arrayData = $item->data;

        foreach ($updates as $update) {

            $statePath = $update['statepath'];
            $newValue = $update['value'];

            if (
                $update['type'] == \Domain\Blueprint\Enums\FieldType::MEDIA &&
                ! is_null($update['value'])
            ) {
                $newValue = [];

                $blueprint_data = $item->blueprintData()->where('state_path', $update['statepath'])->first();

                foreach ($update['value'] as $media_item) {

                    $pathInfo = pathinfo($media_item);

                    if (isset($pathInfo['extension']) && $pathInfo['extension'] !== '') {

                        $media = Media::where('file_name', $media_item)->first();

                        $newValue[] = $media ? $media->getpath() : $media_item;

                    } else {

                        /** @var Media */
                        $media = Media::where('uuid', $media_item)->first();

                        $newValue[] = $media->getPath();
                    }

                }

                if (! $blueprint_data) {

                    $blueprint_data = app(CreateBlueprintDataAction::class)->storeBlueprintData(
                        new BlueprintDataData(
                            blueprint_id: $item->blueprint_id,
                            model_id: $item->id,
                            model_type: $item->getMorphClass(),
                            state_path: $update['statepath'],
                            value: $newValue,
                            type: \Domain\Blueprint\Enums\FieldType::MEDIA
                        )
                    );
                } else {

                    $blueprint_data = $this->updateBlueprintDataAction->updateBlueprintData(
                        $item,
                        new BlueprintDataData(
                            blueprint_id: $item->blueprint_id,
                            model_id: $item->id,
                            model_type: $item->getMorphClass(),
                            state_path: $update['statepath'],
                            value: $newValue,
                            type: \Domain\Blueprint\Enums\FieldType::MEDIA
                        ));

                }

                $newValue = $blueprint_data->getMedia('blueprint_media')->pluck('uuid')->toArray();

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
}
