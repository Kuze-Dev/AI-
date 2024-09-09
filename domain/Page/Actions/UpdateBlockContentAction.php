<?php

declare(strict_types=1);

namespace Domain\Page\Actions;

use Domain\Blueprint\Actions\ExtractDataAction;
use Domain\Blueprint\Actions\UpdateBlueprintDataAction;
use Domain\Blueprint\Traits\SanitizeBlueprintDataTrait;
use Domain\Page\DataTransferObjects\BlockContentData;
use Domain\Page\Models\BlockContent;
use Domain\Page\Models\Page;

class UpdateBlockContentAction
{
    use SanitizeBlueprintDataTrait;

    public function __construct(
        protected UpdateBlueprintDataAction $updateBlueprintDataAction,
    ) {
    }

    public function execute(BlockContent $blockContent, BlockContentData $blockContentData): BlockContent
    {
        $sanitizeData = $this->sanitizeBlueprintData(
            $blockContentData->data ?? [],
            $blockContent->block->blueprint->schema->getFieldStatekeys()
        );

        $blockContent->update([
            'block_id' => $blockContentData->block_id,
            'data' => $sanitizeData ? $sanitizeData : null,
        ]);

        if (tenancy()->tenant?->features()->inactive(\App\Features\CMS\Internationalization::class)) {
            $this->handleBlockContentTranslations($blockContent, $blockContentData);

            return $blockContent;
        }

        $this->updateBlueprintDataAction->execute($blockContent);

        return $blockContent;
    }

    private function handleBlockContentTranslations(BlockContent $blockContent, BlockContentData $blockContentData)
    {

        $extractedDatas = app(ExtractDataAction::class)->extractStatePathAndFieldTypes($blockContent->block->blueprint->schema->sections);

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
            $pageModel = $blockContent->page;

            //check page if page has translation

            if ($pageModel->translation_id) {

                $pageIds = $pageModel->pageTranslation()
                    ->orwhere('id', $pageModel->translation_id)
                    ->orwhere('translation_id', $pageModel->translation_id)
                    ->get()
                    ->pluck('id')
                    ->toArray();
            } else {
                $pageIds = $pageModel->pageTranslation()
                    ->orwhere('id', $pageModel->id)
                    ->get()
                    ->pluck('id')
                    ->toArray();
            }

            //process blockcontents of translated page
            $blockContentList = BlockContent::where('block_id', $blockContentData->block_id)
                ->whereIn('page_id', $pageIds)
                ->where('order', $blockContent->order)
                ->get();

            foreach ($blockContentList as $item) {

                $updated_version = $this->updateJsonByStatePaths($item->data, $filtered);

                $sanitizeUpdatedData = $this->sanitizeBlueprintData(
                    $updated_version,
                    $blockContent->block->blueprint->schema->getFieldStatekeys()
                );

                $item->update([
                    'data' => $sanitizeUpdatedData,
                ]);

                $this->updateBlueprintDataAction->execute($item);
            }

        }

        $this->updateBlueprintDataAction->execute($blockContent);

    }

    private function updateJsonByStatePaths($arrayData, $updates)
    {

        foreach ($updates as $update) {

            $statePath = $update['statepath'];
            $newValue = $update['value'];

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
