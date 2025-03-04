<?php

declare(strict_types=1);

namespace Domain\Internationalization\Actions;

use Domain\Blueprint\Actions\CreateBlueprintDataAction;
use Domain\Blueprint\Actions\ExtractDataAction;
use Domain\Blueprint\Actions\UpdateBlueprintDataAction;
use Domain\Blueprint\DataTransferObjects\BlueprintDataData;
use Domain\Blueprint\Traits\SanitizeBlueprintDataTrait;
use Domain\Content\Models\ContentEntry;
use Domain\Globals\Models\Globals;
use Domain\Page\Models\BlockContent;
use Domain\Taxonomy\Models\TaxonomyTerm;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class HandleDataTranslation
{
    use SanitizeBlueprintDataTrait;

    public function __construct(
        protected CreateBlueprintDataAction $createBlueprintDataAction,
        protected UpdateBlueprintDataAction $updateBlueprintDataAction,
    ) {
    }

    /** Execute create content query.
     *
     * @param  ContentEntry|BlockContent|TaxonomyTerm|Globals  $model,
     * @param  ContentEntry|BlockContent|TaxonomyTerm|Globals  $translationModel
     */
    public function execute(
        Model $model,
        Model $translationModel,
    ): void {

        if ($model instanceof ContentEntry) {
            $blueprintfieldtype = $model->content->blueprint->schema;
        } elseif ($model instanceof BlockContent) {
            $blueprintfieldtype = $model->block->blueprint->schema;
        } elseif ($model instanceof TaxonomyTerm) {
            $blueprintfieldtype = $model->taxonomy->blueprint->schema;
        /**
         *  suggested by copilot
         *  @phpstan-ignore-next-line */
        } elseif ($model instanceof Globals) {
            $blueprintfieldtype = $model->blueprint->schema;
        } else {
            return;
        }

        $extractedDatas = app(ExtractDataAction::class)->extractStatePathAndFieldTypes($blueprintfieldtype->sections);

        /** @var array */
        $combinedArray = [];

        $data = [];

        foreach ($extractedDatas as $sectionKey => $sectionValue) {
            foreach ($sectionValue as $fieldKey => $fieldValue) {
                $combinedArray[$sectionKey][$fieldKey] = app(ExtractDataAction::class)->mergeFields($fieldValue, $model->data[$sectionKey][$fieldKey], $fieldValue['statepath']);
            }
        }

        foreach ($combinedArray as $section) {
            foreach ($section as $field) {
                $data[] = app(ExtractDataAction::class)->processRepeaterField($field);
            }
        }

        $flattenData = app(ExtractDataAction::class)->flattenArray($data);

        $filtered = array_filter($flattenData, fn($item) => isset($item['translatable']) && $item['translatable'] === false);

        if (
            count($filtered) > 0
        ) {
            $data = $this->updateJsonByStatePaths($translationModel, $filtered);

            $translationModel->update([
                'data' => $data,
            ]);

        }
    }

    public function updateJsonByStatePaths(
        ContentEntry|BlockContent|TaxonomyTerm|Globals $item,
        array $updates): array
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

                    $pathInfo = pathinfo((string) $media_item);

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
                        $this->createBlueprintDataDTO(
                            $item,
                            $update['statepath'],
                            \Domain\Blueprint\Enums\FieldType::MEDIA,
                            $newValue
                        )
                    );
                } else {

                    $blueprint_data = $this->updateBlueprintDataAction->updateBlueprintData(
                        $item,
                        $this->createBlueprintDataDTO(
                            $item,
                            $update['statepath'],
                            \Domain\Blueprint\Enums\FieldType::MEDIA,
                            $newValue
                        )

                    );

                }

                $newValue = $blueprint_data->getMedia('blueprint_media')->pluck('uuid')->toArray();

            }

            $keys = explode('.', (string) $statePath);

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

    protected function createBlueprintDataDTO(
        ContentEntry|BlockContent|TaxonomyTerm|Globals $model,
        string $statePath,
        \Domain\Blueprint\Enums\FieldType $fieldType,
        null|string|array|bool $newValue
    ): BlueprintDataData {

        $blueprint_id = match ($model::class) {
            ContentEntry::class => $model->content->blueprint_id,
            BlockContent::class => $model->block->blueprint_id,
            TaxonomyTerm::class => $model->taxonomy->blueprint_id,
            Globals::class => $model->blueprint_id,
            default => null
        };

        if (! $blueprint_id) {
            abort(422, 'Blueprint id is null');
        }

        return new BlueprintDataData(
            blueprint_id: $blueprint_id,
            model_id: $model->id,
            model_type: $model->getMorphClass(),
            state_path: $statePath,
            value: $newValue,
            type: $fieldType
        );
    }
}
