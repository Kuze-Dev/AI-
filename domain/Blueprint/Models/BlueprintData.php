<?php

declare(strict_types=1);

namespace Domain\Blueprint\Models;

use Domain\Blueprint\DataTransferObjects\FieldData;
use Domain\Blueprint\DataTransferObjects\RepeaterFieldData;
use Domain\Blueprint\Enums\BlueprintDataType;
use Domain\Blueprint\Enums\FieldType;
use Domain\Blueprint\Enums\ManipulationType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Eloquent;
use Filament\Forms\Components\Field;

/**
 * Domain\Blueprint\Models\BlueprintData
 *
 * @property int $id
 * @property string $blueprint_id
 * @property string $model_type
 * @property int $model_id
 * @property string $state_path
 * @property string|null $value
 * @property string $type
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Domain\Blueprint\Models\Blueprint|null $blueprint
 * @property-read \Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection<int, Media> $media
 * @property-read int|null $media_count
 * @property-read Model|Eloquent $model
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
 * @mixin \Eloquent
 */
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
        'value' => 'array',
    ];

    /** @return BelongsTo<Blueprint, BlueprintData> */
    public function blueprint(): BelongsTo
    {
        return $this->belongsTo(Blueprint::class);
    }

    /** @return MorphTo<Model, self> */
    public function model(): MorphTo
    {
        return $this->morphTo();
    }

    /** @var bool */
    public $registerMediaConversionsUsingModelInstance = true;

    public function registerMediaConversions(Media $media = null): void
    {
        $blueprint = $this->blueprint;
        if( ! $blueprint) {
            return;
        }
        $schema = $blueprint->schema;
        foreach ($schema->sections as $section) {
            foreach ($section->fields as $field) {
                $this->processRepeaterField($field, $section->state_name);
            }
        }

    }

    protected function processRepeaterField(RepeaterFieldData|FieldData $field, string $currentpath): void
    {
        $statePath = $currentpath . '.' . $field->state_name;
        if($field->type === FieldType::REPEATER) {
            if (property_exists($field, 'fields') && is_array($field->fields)) {
                foreach($field->fields as $repeaterFields) {
                    $this->processRepeaterField($repeaterFields, $statePath);
                }
            }
         
        }
        if ($field->type === FieldType::MEDIA) {
            if ($statePath === $this->state_path) {
                foreach ($field->conversions ?? [] as $conversion) {
                    $title = $conversion->name;
                    $width = null;
                    $height = null;
                    if (isset($conversion->manipulations)) {
                        foreach($conversion->manipulations as $manipulation) {
                            if($manipulation->type == ManipulationType::WIDTH) {
                                $width = $manipulation->params[0];
                            }
                            if($manipulation->type == ManipulationType::HEIGHT) {
                                $height = $manipulation->params[0];
                            }
                        }
                        /** @phpstan-ignore-next-line */
                        $this->addMediaConversion($title)
                            ->width($width)
                            ->height($height)
                            ->keepOriginalImageFormat();
                    }
                }
            }
        }
    }
}
