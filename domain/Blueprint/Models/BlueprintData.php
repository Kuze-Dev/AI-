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

    public function registerMediaCollections(): void
    {

        if ( ! $this->blueprint) {
            return;
        }
        $config = $this->blueprint->schema;

        foreach ($config->sections as $section) {
            foreach ($section->fields as $field) {
                if ($field->type === FieldType::MEDIA) {
                    foreach ($field->conversions as $conversion) {
                        $title = $conversion->name;
                        if (isset($conversion->manipulations)) {
                            foreach($conversion->manipulations as $manipulation) {
                                if($manipulation->type == ManipulationType::WIDTH) {
                                    $width = $manipulation->params[0];
                                }
                                if($manipulation->type == ManipulationType::HEIGHT) {
                                    $height = $manipulation->params[0];
                                }
                            }
                            $registerMediaConversions = function (Media $media) use ($width, $height, $title) {
                                $this->addMediaConversion($title)
                                    ->width(12)
                                    ->height(23);
                            };


                            $mediaCollection = $this->addMediaCollection('blueprint_media');
                            $mediaCollection->registerMediaConversions($registerMediaConversions);
                            
                        }
                    }
                }
            }
        }
    }
}
