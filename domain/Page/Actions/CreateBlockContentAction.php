<?php

declare(strict_types=1);

namespace Domain\Page\Actions;

use Domain\Blueprint\Actions\CreateBlueprintDataAction;
use Domain\Blueprint\Actions\ExtractDataAction;
use Domain\Blueprint\Actions\UpdateBlueprintDataAction;
use Domain\Blueprint\DataTransferObjects\BlueprintDataData;
use Domain\Blueprint\Traits\SanitizeBlueprintDataTrait;
use Domain\Page\DataTransferObjects\BlockContentData;
use Domain\Page\Models\BlockContent;
use Domain\Page\Models\Page;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class CreateBlockContentAction
{
    use SanitizeBlueprintDataTrait;

    public function __construct(
        protected CreateBlueprintDataAction $createBlueprintDataAction,
        protected UpdateBlueprintDataAction $updateBlueprintDataAction,
    ) {
    }

    public function execute(Page $page, BlockContentData $blockContentData): BlockContent
    {
        $blockContent = $page->blockContents()->create([
            'block_id' => $blockContentData->block_id,
            'data' => $blockContentData->data,
        ]);

        $this->createBlueprintDataAction->execute($blockContent);

        if (tenancy()->tenant?->features()->active(\App\Features\CMS\Internationalization::class)) {
            $this->handleBlockContentTranslations($blockContent, $blockContentData);

            return $blockContent;
        }

        return $blockContent;
    }

    private function handleBlockContentTranslations(BlockContent $blockContent, BlockContentData $blockContentData): void
    {

        if (! $blockContent->data) {
            return;
        }

        $extractedDatas = app(ExtractDataAction::class)->extractStatePathAndFieldTypes($blockContent->block->blueprint->schema->sections);

        /** @var array */
        $combinedArray = [];

        $data = [];

        foreach ($extractedDatas as $sectionKey => $sectionValue) {
            foreach ($sectionValue as $fieldKey => $fieldValue) {
                $combinedArray[$sectionKey][$fieldKey] = app(ExtractDataAction::class)->mergeFields($fieldValue, $blockContent->data[$sectionKey][$fieldKey], $fieldValue['statepath']);
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

            $updated_version = $this->updateJsonByStatePaths($blockContent, $filtered);

            $sanitizeUpdatedData = $this->sanitizeBlueprintData(
                $updated_version,
                $blockContent->block->blueprint->schema->getFieldStatekeys()
            );

            $blockContent->update([
                'data' => $sanitizeUpdatedData,
            ]);
        }

    }

    private function updateJsonByStatePaths(BlockContent $item, array $updates): ?array
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
                        $newValue[] = $media_item;
                    } else {

                        /** @var Media */
                        $media = Media::where('uuid', $media_item)->first();

                        $newValue[] = $media->getPath();
                    }

                }

                $blueprint_data = $this->updateBlueprintDataAction->updateBlueprintData(
                    $item,
                    new BlueprintDataData(
                        blueprint_id: $item->block->blueprint_id,
                        model_id: $item->id,
                        model_type: $item->getMorphClass(),
                        state_path: $update['statepath'],
                        value: $newValue,
                        type: \Domain\Blueprint\Enums\FieldType::MEDIA
                    ));

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
