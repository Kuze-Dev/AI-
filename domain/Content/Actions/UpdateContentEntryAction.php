<?php

declare(strict_types=1);

namespace Domain\Content\Actions;

use Domain\Blueprint\Actions\CreateBlueprintDataAction;
use Domain\Blueprint\Actions\ExtractDataAction;
use Domain\Blueprint\Actions\UpdateBlueprintDataAction;
use Domain\Blueprint\DataTransferObjects\BlueprintDataData;
use Domain\Blueprint\Traits\SanitizeBlueprintDataTrait;
use Domain\Content\DataTransferObjects\ContentEntryData;
use Domain\Content\Models\ContentEntry;
use Domain\Internationalization\Actions\HandleUpdateDataTranslation;
use Domain\Internationalization\Models\Locale;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Support\MetaData\Actions\CreateMetaDataAction;
use Support\MetaData\Actions\UpdateMetaDataAction;
use Support\RouteUrl\Actions\CreateOrUpdateRouteUrlAction;

class UpdateContentEntryAction
{
    use SanitizeBlueprintDataTrait;

    public function __construct(
        protected CreateMetaDataAction $createMetaData,
        protected UpdateMetaDataAction $updateMetaData,
        protected CreateOrUpdateRouteUrlAction $createOrUpdateRouteUrl,
        protected UpdateBlueprintDataAction $updateBlueprintDataAction,
    ) {
    }

    /**
     * Execute operations for updating
     * and save content entry query.
     */
    public function execute(ContentEntry $contentEntry, ContentEntryData $contentEntryData): ContentEntry
    {
        $sanitizeData = $this->sanitizeBlueprintData(
            $contentEntryData->data,
            $contentEntry->content->blueprint->schema->getFieldStatekeys(),
        );

        $contentEntry->update([
            'author_id' => $contentEntryData->author_id,
            'title' => $contentEntryData->title,
            'published_at' => $contentEntryData->published_at,
            'data' => $sanitizeData,
            'locale' => $contentEntryData->locale ?? Locale::where('is_default', true)->first()?->code,
            'status' => $contentEntryData->status,
        ]);

        $contentEntry->metaData()->exists()
            ? $this->updateMetaData->execute($contentEntry, $contentEntryData->meta_data)
            : $this->createMetaData->execute($contentEntry, $contentEntryData->meta_data);

        $contentEntry->taxonomyTerms()
            ->sync($contentEntryData->taxonomy_terms);

        $this->createOrUpdateRouteUrl->execute($contentEntry, $contentEntryData->route_url_data);

        if (tenancy()->tenant?->features()->active(\App\Features\CMS\SitesManagement::class)) {

            $contentEntry->sites()->sync($contentEntryData->sites);
        }

        if (tenancy()->tenant?->features()->active(\App\Features\CMS\Internationalization::class)) {

            app(HandleUpdateDataTranslation::class)->execute($contentEntry, $contentEntryData);
            // $this->handleContentEntryTranslations($contentEntry, $contentEntryData);

            return $contentEntry;
        }

        $this->updateBlueprintDataAction->execute($contentEntry);

        return $contentEntry;
    }

    // private function handleContentEntryTranslations(ContentEntry $contentEntry, ContentEntryData $contentEntryData): void
    // {

    //     if (! $contentEntry->data) {
    //         return;
    //     }

    //     $extractedDatas = app(ExtractDataAction::class)->extractStatePathAndFieldTypes($contentEntry->content->blueprint->schema->sections);

    //     /** @var array */
    //     $combinedArray = [];

    //     $data = [];

    //     foreach ($extractedDatas as $sectionKey => $sectionValue) {
    //         foreach ($sectionValue as $fieldKey => $fieldValue) {
    //             $combinedArray[$sectionKey][$fieldKey] = app(ExtractDataAction::class)->mergeFields($fieldValue, $contentEntry->data[$sectionKey][$fieldKey], $fieldValue['statepath']);
    //         }
    //     }

    //     foreach ($combinedArray as $section) {
    //         foreach ($section as $field) {
    //             $data[] = app(ExtractDataAction::class)->processRepeaterField($field);
    //         }
    //     }

    //     $flattenData = app(ExtractDataAction::class)->flattenArray($data);

    //     $filtered = array_filter($flattenData, function ($item) {
    //         return isset($item['translatable']) && $item['translatable'] === false;
    //     });

    //     if (
    //         count($filtered) > 0
    //     ) {

    //         //check page if page has translation

    //         if ($contentEntry->translation_id) {

    //             $contentEntry_collection = $contentEntry->contentEntryTranslation()
    //                 ->orwhere('id', $contentEntry->translation_id)
    //                 ->orwhere('translation_id', $contentEntry->translation_id)
    //                 ->get();

    //         } else {
    //             $contentEntry_collection = $contentEntry->contentEntryTranslation()
    //                 ->orwhere('id', $contentEntry->id)
    //                 ->get();

    //         }

    //         foreach ($contentEntry_collection as $item) {

    //             $updated_version = $this->updateJsonByStatePaths($item, $filtered, $contentEntry);

    //             $sanitizeUpdatedData = $this->sanitizeBlueprintData(
    //                 $updated_version,
    //                 $contentEntry->content->blueprint->schema->getFieldStatekeys()
    //             );

    //             $item->update([
    //                 'data' => $sanitizeUpdatedData,
    //             ]);

    //             $this->updateBlueprintDataAction->execute($item);

    //         }

    //         return;
    //     }

    //     $this->updateBlueprintDataAction->execute($contentEntry);

    // }

    // private function updateJsonByStatePaths(ContentEntry $item, array $updates, ContentEntry $source): array
    // {

    //     $arrayData = $item->data;

    //     foreach ($updates as $update) {

    //         $statePath = $update['statepath'];
    //         $newValue = $update['value'];

    //         if ($item->id != $source->id &&
    //             $update['type'] == \Domain\Blueprint\Enums\FieldType::MEDIA &&
    //             ! is_null($update['value'])
    //         ) {
    //             $newValue = [];

    //             $blueprint_data = $item->blueprintData()->where('state_path', $update['statepath'])->first();

    //             foreach ($update['value'] as $media_item) {

    //                 $pathInfo = pathinfo($media_item);

    //                 if (isset($pathInfo['extension']) && $pathInfo['extension'] !== '') {

    //                     $media = Media::where('file_name', $media_item)->first();

    //                     $newValue[] = $media ? $media->getpath() : $media_item;

    //                 } else {

    //                     $media = Media::where('uuid', $media_item)->first();

    //                     $newValue[] = $media?->getPath();
    //                 }

    //             }

    //             $newValue = array_filter($newValue, fn ($value) => ! is_null($value));

    //             if (! $blueprint_data) {

    //                 $blueprint_data = app(CreateBlueprintDataAction::class)->storeBlueprintData(
    //                     new BlueprintDataData(
    //                         blueprint_id: $item->content->blueprint_id,
    //                         model_id: $item->id,
    //                         model_type: $item->getMorphClass(),
    //                         state_path: $update['statepath'],
    //                         value: $newValue,
    //                         type: \Domain\Blueprint\Enums\FieldType::MEDIA
    //                     )
    //                 );
    //             } else {

    //                 $blueprint_data = $this->updateBlueprintDataAction->updateBlueprintData(
    //                     $item,
    //                     new BlueprintDataData(
    //                         blueprint_id: $item->content->blueprint_id,
    //                         model_id: $item->id,
    //                         model_type: $item->getMorphClass(),
    //                         state_path: $update['statepath'],
    //                         value: $newValue,
    //                         type: \Domain\Blueprint\Enums\FieldType::MEDIA
    //                     ));

    //             }

    //             $newValue = $blueprint_data->getMedia('blueprint_media')->pluck('uuid')->toArray();

    //         }

    //         $keys = explode('.', $statePath);

    //         $temp = &$arrayData;

    //         // Traverse the array using the keys from the state path
    //         foreach ($keys as $key) {
    //             // If the key doesn't exist, create it as an array
    //             if (! isset($temp[$key])) {
    //                 $temp[$key] = [];
    //             }

    //             // Move deeper into the array
    //             $temp = &$temp[$key];
    //         }

    //         // Set the final key to the new value
    //         $temp = $newValue;
    //     }

    //     // Return the updated array
    //     return $arrayData;
    // }
}
