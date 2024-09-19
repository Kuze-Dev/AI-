<?php

declare(strict_types=1);

namespace Domain\Content\Actions;

use Domain\Blueprint\Actions\CreateBlueprintDataAction;
use Domain\Blueprint\Actions\ExtractDataAction;
use Domain\Blueprint\Actions\UpdateBlueprintDataAction;
use Domain\Blueprint\DataTransferObjects\BlueprintDataData;
use Domain\Content\DataTransferObjects\ContentEntryData;
use Domain\Content\Models\Content;
use Domain\Content\Models\ContentEntry;
use Domain\Internationalization\Models\Locale;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Support\MetaData\Actions\CreateMetaDataAction;
use Support\RouteUrl\Actions\CreateOrUpdateRouteUrlAction;

class CreateContentEntryTranslationAction
{
    public function __construct(
        protected CreateMetaDataAction $createMetaData,
        protected CreateOrUpdateRouteUrlAction $createOrUpdateRouteUrl,
        protected CreateBlueprintDataAction $createBlueprintDataAction,
        protected UpdateBlueprintDataAction $updateBlueprintDataAction,
    ) {
    }

    /** Execute create content entry query. */
    public function execute(ContentEntry $content, ContentEntryData $contentEntryData): ContentEntry
    {
        /** @var ContentEntry $contentEntryTranslation */
        $contentEntryTranslation = $content->contentEntryTranslation()
            ->create([
                'title' => $contentEntryData->title,
                'data' => $contentEntryData->data,
                'content_id' => $content->content_id,
                'published_at' => $contentEntryData->published_at,
                'author_id' => $contentEntryData->author_id,
                'locale' => $contentEntryData->locale ?? Locale::where('is_default', true)->first()?->code,
                'status' => $contentEntryData->status,
            ]);

        $this->createBlueprintDataAction->execute($contentEntryTranslation);

        $this->createMetaData->execute($contentEntryTranslation, $contentEntryData->meta_data);

        $contentEntryTranslation->taxonomyTerms()
            ->attach($contentEntryData->taxonomy_terms);

        $this->createOrUpdateRouteUrl->execute($contentEntryTranslation, $contentEntryData->route_url_data);

        if (tenancy()->tenant?->features()->active(\App\Features\CMS\SitesManagement::class)) {

            $contentEntryTranslation->sites()->sync($contentEntryData->sites);
        }

        $this->handleContentEntryDataTranslation($content, $contentEntryTranslation);

        $contentEntryTranslation->refresh();

        return $contentEntryTranslation;

    }

    private function handleContentEntryDataTranslation(ContentEntry $contentEntry, ContentEntry $translatedContentEntry): void
    {
        $extractedDatas = app(ExtractDataAction::class)->extractStatePathAndFieldTypes($contentEntry->content->blueprint->schema->sections);

        /** @var array */
        $combinedArray = [];

        $data = [];

        foreach ($extractedDatas as $sectionKey => $sectionValue) {
            foreach ($sectionValue as $fieldKey => $fieldValue) {
                $combinedArray[$sectionKey][$fieldKey] = app(ExtractDataAction::class)->mergeFields($fieldValue, $contentEntry->data[$sectionKey][$fieldKey], $fieldValue['statepath']);
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
            $data = $this->updateJsonByStatePaths($translatedContentEntry, $filtered);

            $translatedContentEntry->update([
                'data' => $data,
            ]);

        }
    }

    private function updateJsonByStatePaths(ContentEntry $item, array $updates): array
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
                            blueprint_id: $item->content->blueprint_id,
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
                            blueprint_id: $item->content->blueprint_id,
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
