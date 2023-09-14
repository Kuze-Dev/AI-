<?php

declare(strict_types=1);

namespace Domain\Blueprint\Actions;

use Domain\Blueprint\DataTransferObjects\BlueprintDataData;
use Domain\Blueprint\Enums\FieldType;
use Domain\Blueprint\Models\BlueprintData;
use Domain\Page\Models\BlockContent;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class UpdateBlueprintDataAction
{
    public function __construct(
        protected ExtractDataAction $extractDataAction,
    ) {
    }

    public function execute(BlockContent $blockContent): void
    {
        $blueprintfieldtype = $blockContent->block->blueprint->schema;
        if( ! $blockContent->data) {
            return;
        }
        $statePaths = $this->extractDataAction->extractStatePath($blockContent->data);
        $fieldTypes = $this->extractDataAction->extractFieldType($blueprintfieldtype, $statePaths);

        if($blockContent->block->blueprint->name == 'imagex') {

            // dump($blockContent);
            // dump($blockContent->data);
            // dump($this->extractDataAction->extractStatePath($blockContent->data));\
            // dump($blueprintfieldtype);
            dump($statePaths);
            dump($fieldTypes);
            dd('x');
            // dump( $this->extractDataAction->testState($blockContent->data));
            // dump(  $this->extractDataAction->test($blueprintfieldtype, $statePaths));
            // dd(123);
            // dd(array_combine($statePaths, $fieldTypes));
        }
        foreach (array_combine($statePaths, $fieldTypes) as $statePath => $fieldType) {
            $this->updateBlueprintData(BlueprintDataData::fromArray($blockContent, $statePath, $fieldType));
        }

    }

    private function updateBlueprintData(BlueprintDataData $blueprintDataData): BlueprintData
    {
        $blueprintData = BlueprintData::where('model_id', $blueprintDataData->model_id)->where('state_path', $blueprintDataData->state_path)->first();
        if( ! $blueprintData) {
            return new BlueprintData();
        }

        if ($blueprintData->type == FieldType::MEDIA->value) {
            if( ! $blueprintDataData->value) {
                return $blueprintData;
            }
            
            if (is_array($blueprintDataData->value)) {
                $toUpload = $blueprintDataData->value;
                $currentUploaded = $blueprintDataData->value;
    
                #filter array with value that has filename extension
    
                $filtered = array_filter($toUpload, function ($value) {
                    $pathInfo = pathinfo($value);
                    if (isset($pathInfo['extension']) && $pathInfo['extension'] !== '') {
                        return $value;
                    }
                });
    
                # filter $blueprintDataData->value array with value that has no filename extension
    
                $currentMedia = array_filter($currentUploaded, function ($value) {
                    $pathInfo = pathinfo($value);
    
                    if ( ! array_key_exists('extension', $pathInfo)) {
                        return $value;
                    }
                });
    
                foreach($filtered as $image) {
                    $blueprintData->addMediaFromDisk($image, 's3')
                        ->toMediaCollection('blueprint_media');
    
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
}
