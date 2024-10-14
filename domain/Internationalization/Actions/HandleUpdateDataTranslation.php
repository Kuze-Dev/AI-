<?php

declare(strict_types=1);

namespace Domain\Internationalization\Actions;

use App\Settings\CustomerSettings;
use Domain\Blueprint\Actions\CreateBlueprintDataAction;
use Domain\Blueprint\Actions\ExtractDataAction;
use Domain\Blueprint\Actions\UpdateBlueprintDataAction;
use Domain\Blueprint\DataTransferObjects\BlueprintDataData;
use Domain\Blueprint\Models\Blueprint;
use Domain\Blueprint\Traits\SanitizeBlueprintDataTrait;
use Domain\Content\Models\ContentEntry;
use Domain\Customer\Models\Customer;
use Domain\Globals\Models\Globals;
use Domain\Internationalization\DataTransferObjects\TranslationDTO;
use Domain\Page\Models\BlockContent;
use Domain\Page\Models\Page;
use Domain\Taxonomy\Models\TaxonomyTerm;
use ErrorException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class HandleUpdateDataTranslation
{
    use SanitizeBlueprintDataTrait;

    public function __construct(
        protected CreateBlueprintDataAction $createBlueprintDataAction,
        protected UpdateBlueprintDataAction $updateBlueprintDataAction,
    ) {
    }

    /** Execute create content query. */
    public function execute(
        Model $model,
        TranslationDTO $modelDTO,
    ): void {

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

        $filtered = array_filter($flattenData, function ($item) {
            return isset($item['translatable']) && $item['translatable'] === false;
        });

        if (
            count($filtered) > 0
        ) {

            $translation_collection = $this->getTranslatableModelCollection($model, $modelDTO);

            foreach ($translation_collection as $item) {

                $updated_version = $this->updateJsonByStatePaths($item, $filtered, $model);

                $sanitizeUpdatedData = $this->sanitizeBlueprintData(
                    $updated_version,
                    $blueprintfieldtype->getFieldStatekeys()
                );

                $item->update([
                    'data' => $sanitizeUpdatedData,
                ]);

                $this->updateBlueprintDataAction->execute($item);

            }

            return;

        }
    }

    /**
     * @param  ContentEntry|BlockContent|Customer|TaxonomyTerm|Globals  $item
     * @param  ContentEntry|BlockContent|Customer|TaxonomyTerm|Globals  $source
     */
    private function updateJsonByStatePaths(Model $item, array $updates, Model $source): array
    {

        $arrayData = $item->data;

        foreach ($updates as $update) {

            $statePath = $update['statepath'];
            $newValue = $update['value'];

            if ($item->id != $source->id &&
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

    /**
     * @param  ContentEntry|BlockContent|Customer|TaxonomyTerm|Globals  $model
     */
    protected function createBlueprintDataDTO(
        Model $model,
        string $statePath,
        \Domain\Blueprint\Enums\FieldType $fieldType,
        null|string|array|bool $newValue
    ): BlueprintDataData {

        $blueprint_id = match ($model::class) {
            ContentEntry::class => $model->content->blueprint_id,
            BlockContent::class => $model->block->blueprint_id,
            TaxonomyTerm::class => $model->taxonomy->blueprint_id,
            Globals::class => $model->blueprint_id,
            default => throw new ErrorException(
                'Model '.$model::class.'::'.' doest not support data translation.'
            ),
        };

        return new BlueprintDataData(
            blueprint_id: $blueprint_id,
            model_id: $model->id,
            model_type: $model->getMorphClass(),
            state_path: $statePath,
            value: $newValue,
            type: $fieldType
        );
    }

    /**
     * @param  ContentEntry|BlockContent|Customer|TaxonomyTerm|Globals  $model,
     * @param  mixed  $modelDTO
     */
    /** @phpstan-ignore-next-line */
    protected function getTranslatableModelCollection($model, $modelDTO): Collection
    {

        if ($model instanceof BlockContent) {

            /** @var Page */
            $pageModel = $model->page;

            if ($pageModel->translation_id) {

                $pageIds = $pageModel->dataTranslation()
                    ->orwhere('id', $pageModel->translation_id)
                    ->orwhere('translation_id', $pageModel->translation_id)
                    ->get()
                    ->pluck('id')
                    ->toArray();
            } else {
                $pageIds = $pageModel->dataTranslation()
                    ->orwhere('id', $pageModel->id)
                    ->get()
                    ->pluck('id')
                    ->toArray();
            }

            return BlockContent::where('block_id', $modelDTO->block_id)
                ->whereIn('page_id', $pageIds)
                ->where('order', $model->order)
                ->get();
        }

        if ($model->translation_id) {

            $translation_collection = $model->dataTranslation()
                ->orwhere('id', $model->translation_id)
                ->orwhere('translation_id', $model->translation_id)
                ->get();

        } else {
            $translation_collection = $model->dataTranslation()
                ->orwhere('id', $model->id)
                ->get();

        }

        return $translation_collection;
    }
}
