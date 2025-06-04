<?php

declare(strict_types=1);

namespace Domain\Blueprint\Models;

use Domain\Blueprint\DataTransferObjects\FieldData;
use Domain\Blueprint\DataTransferObjects\MediaFieldData;
use Domain\Blueprint\DataTransferObjects\RepeaterFieldData;
use Domain\Blueprint\Enums\BlueprintDataType;
use Domain\Blueprint\Enums\FieldType;
use Domain\Blueprint\Enums\ManipulationType;
use Eloquent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * Domain\Blueprint\Models\BlueprintData
 *
 * @property int $id
 * @property string $blueprint_id
 * @property string $model_type
 * @property int $model_id
 * @property string $state_path
 * @property string|null $value
 * @property array|null $blueprint_media_conversion
 * @property string $type
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Domain\Blueprint\Models\Blueprint|null $blueprint
 * @property-read \Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection<int, Media> $media
 * @property-read int|null $media_count
 * @property-read Model|Eloquent $model
 *
 * @method static \Illuminate\Database\Eloquent\Builder|BlueprintData newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|BlueprintData newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|BlueprintData query()
 * @method static \Illuminate\Database\Eloquent\Builder|BlueprintData whereBlueprintId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BlueprintData whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BlueprintData whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BlueprintData whereModelId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BlueprintData whereModelType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BlueprintData whereStatePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BlueprintData whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BlueprintData whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BlueprintData whereValue($value)
 *
 * @mixin \Eloquent
 */
class BlueprintData extends Model implements HasMedia
{
    /** @use InteractsWithMedia<\Spatie\MediaLibrary\MediaCollections\Models\Media> */
    use InteractsWithMedia;

    protected $fillable = [
        'blueprint_id',
        'model_id',
        'model_type',
        'state_path',
        'blueprint_media_conversion',
        'value',
        'type',
    ];

    protected function casts(): array
    {
        return [
            // 'type' => BlueprintDataType::class,
            'blueprint_media_conversion' => 'array',
            'value' => 'array',
        ];
    }

    /** @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\Domain\Blueprint\Models\Blueprint, $this> */
    public function blueprint(): BelongsTo
    {
        return $this->belongsTo(Blueprint::class);
    }

    /** @return MorphTo<Model, $this> */
    public function model(): MorphTo
    {
        return $this->morphTo();
    }

    /** @return MorphTo<Model, $this> */
    public function resourceModel(): MorphTo
    {
        return $this->morphTo(__FUNCTION__, 'model_type', 'model_id');
    }

    public bool $registerMediaConversionsUsingModelInstance = true;

    #[\Override]
    public function registerMediaConversions(?Media $media = null): void
    {
        /**
         * TODO: Remove old implementation of media conversions
         * once all tenants is migrated to the process.
         *
         * Each media conversion will be defined in the blueprintdata model to handle
         * conversion properly and to avoid loss of data when the blueprint is updated.
         */
        if (! is_null($this->blueprint_media_conversion) && count($this->blueprint_media_conversion) > 0) {
            foreach ($this->blueprint_media_conversion as $conversion) {
                $title = $conversion['name'];
                $width = $conversion['width'] ?? null;
                $height = $conversion['height'] ?? null;
                $type = $conversion['type'] ?? null;
                // $fit = 'contain';
                $fit = Fit::Contain;
                if (isset($conversion['manipulations'])) {
                    foreach ($conversion['manipulations'] as $manipulation) {
                        if ($manipulation['type'] === ManipulationType::WIDTH->value) {
                            $width = $manipulation['params'][0];
                        }
                        if ($manipulation['type'] === ManipulationType::HEIGHT->value) {
                            $height = $manipulation['params'][0];
                        }
                        if ($manipulation['type'] === ManipulationType::TYPE->value) {
                            if (! empty($manipulation['params'][0])) {
                                $type = $manipulation['params'][0];
                            }
                        }
                        if ($manipulation['type'] === ManipulationType::FIT->value) {
                            // $fit = $manipulation->params[0];
                            /** @phpstan-ignore match.unhandled */
                            $fit = match ($manipulation['params'][0]) {
                                'contain' => Fit::Contain,
                                'max' => Fit::Max,
                                'fill' => Fit::Fill,
                                'fill-max' => Fit::FillMax,
                                'stretch' => Fit::Stretch,
                                'crop' => Fit::Crop,
                            };
                        }
                    }

                    if ($type) {
                        $this->addMediaConversion($title)
                            ->width($width)
                            ->height($height)
                            ->format($type)
                            // ->sharpen(10)
                            // ->quality(90)
                            ->fit($fit, $width, $height);
                    } else {
                        /** @phpstan-ignore method.notFound */
                        $this->addMediaConversion($title)
                            ->width($width)
                            ->height($height)
                            // ->sharpen(10)
                            // ->quality(90)
                            ->keepOriginalImageFormat()
                            ->fit($fit, $width, $height);
                    }
                }
            }

        } else {

            $blueprint = $this->blueprint;
            if (! $blueprint) {
                return;
            }
            $schema = $blueprint->schema;
            foreach ($schema->sections as $section) {
                foreach ($section->fields as $field) {
                    $this->processRepeaterField($field, $section->state_name);
                }
            }
        }

    }

    protected function processRepeaterField(RepeaterFieldData|FieldData|MediaFieldData $field, string $currentpath): void
    {
        $statePath = $currentpath.'.'.$field->state_name;
        if ($field->type === FieldType::REPEATER) {
            if (property_exists($field, 'fields') && is_array($field->fields)) {
                foreach ($field->fields as $repeaterFields) {
                    $this->processRepeaterField($repeaterFields, $statePath);
                }
            }

        }
        if ($field->type === FieldType::MEDIA) {
            $arrayStatepath = explode('.', $this->state_path);
            foreach ($arrayStatepath as $newStatepath) {
                if (is_numeric($newStatepath)) {
                    $arrayStatepath = array_diff($arrayStatepath, [$newStatepath]);
                }
            }
            $newStatepath = implode('.', $arrayStatepath);
            if ($statePath === $newStatepath) {
                foreach ($field->conversions ?? [] as $conversion) {
                    $title = $conversion->name;
                    $width = null;
                    $height = null;
                    $type = null;
                    // $fit = 'contain';
                    $fit = Fit::Contain;
                    if (isset($conversion->manipulations)) {
                        foreach ($conversion->manipulations as $manipulation) {
                            if ($manipulation->type === ManipulationType::WIDTH) {
                                $width = $manipulation->params[0];
                            }
                            if ($manipulation->type === ManipulationType::HEIGHT) {
                                $height = $manipulation->params[0];
                            }
                            if ($manipulation->type === ManipulationType::TYPE) {
                                if (! empty($manipulation->params[0])) {
                                    $type = $manipulation->params[0];
                                }
                            }
                            if ($manipulation->type === ManipulationType::FIT) {
                                // $fit = $manipulation->params[0];
                                /** @phpstan-ignore match.unhandled */
                                $fit = match ($manipulation->params[0]) {
                                    'contain' => Fit::Contain,
                                    'max' => Fit::Max,
                                    'fill' => Fit::Fill,
                                    'fill-max' => Fit::FillMax,
                                    'stretch' => Fit::Stretch,
                                    'crop' => Fit::Crop,
                                };
                            }
                        }

                        if ($type) {
                            $this->addMediaConversion($title)
                                ->width($width)
                                ->height($height)
                                ->format($type)
                                // ->sharpen(10)
                                // ->quality(90)
                                ->fit($fit, $width, $height);
                        } else {
                            /** @phpstan-ignore method.notFound */
                            $this->addMediaConversion($title)
                                ->width($width)
                                ->height($height)
                                // ->sharpen(10)
                                // ->quality(90)
                                ->keepOriginalImageFormat()
                                ->fit($fit, $width, $height);
                        }
                    }
                }
            }
        }
    }
}
