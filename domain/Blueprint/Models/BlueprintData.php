<?php

declare(strict_types=1);

namespace Domain\Blueprint\Models;

use Domain\Blueprint\DataTransferObjects\MediaFieldData;
use Domain\Blueprint\Enums\BlueprintDataType;
use Domain\Blueprint\Enums\FieldType;
use Domain\Blueprint\Enums\ManipulationType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class BlueprintData extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $fillable = [
        'blueprint_id',
        'model_id',
        'model_type',
        'state_path',
        'value',
        'type',
    ];

    protected $casts = [
        // 'type' => BlueprintDataType::class,
        // 'value' => MediaFieldData::class, // TODO: DTO
    ];

    public function blueprint(): BelongsTo
    {
        return $this->belongsTo(Blueprint::class);
    }

    public function model(): MorphTo
    {
        return $this->morphTo();
    }

    // public function registerMediaCollections(): void
    // {
    //     $fieldData = $this->blueprint->findField($this->state_path);

    //     if (! $fieldData instanceof MediaFieldData) {
    //         return;
    //     }

    //     $mediaCollection = $this->addMediaCollection('default')
    //         ->registerMediaConversions(function () {
    //             foreach($fieldData->conversions as $conversionData) {
    //                 $conversion = $this->addMediaConversion($conversionData->name);

    //                 foreach ($conversionData->manipulations as $manipulationData) {
    //                     $conversion->{$manipulationData->type->value}(...$manipulationData->params);
    //                 }
    //             }
    //         });

    //     if(!$fieldData->multiple){
    //         $mediaCollection->isSingleFile();
    //     }
    // }

    public function registerMediaCollections(): void
    {
        $width = null;
        $height = null;

        $config = $this->blueprint->schema;

        foreach ($config->sections as $section) {
            foreach ($section->fields as $field) {
                if ($field->type === FieldType::MEDIA) {
                    foreach ($field->conversions as $conversion) {
                        if (isset($conversion->manipulations)) {
                            foreach($conversion->manipulations as $manipulation) {
                                if($manipulation->type == ManipulationType::WIDTH) {
                                    $width = $manipulation->params[0];
                                }
                                if($manipulation->type == ManipulationType::HEIGHT) {
                                    $height = $manipulation->params[0];
                                }
                            }
                        }
                    }
                }
            }
        }
        dd($width, $height);
        $registerMediaConversions = function (Media $media) {

            $this->addMediaConversion('preview')
                ->width($this->width)
                ->height($this->height);
        };

        $this->addMediaCollection('blueprint_media')
            ->registerMediaConversions($registerMediaConversions);
    }
}
