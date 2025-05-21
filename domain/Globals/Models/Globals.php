<?php

declare(strict_types=1);

namespace Domain\Globals\Models;

use Domain\Blueprint\Models\Blueprint;
use Domain\Blueprint\Models\BlueprintData;
use Domain\Internationalization\Concerns\HasInternationalizationInterface;
use Domain\Site\Traits\Sites;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;
use Support\ConstraintsRelationships\Attributes\OnDeleteCascade;
use Support\ConstraintsRelationships\ConstraintsRelationships;

/**
 * Domain\Globals\Models\Globals
 *
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string $blueprint_id
 * @property string $locale
 * @property string|null $translation_id
 * @property mixed|null $data
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
 * @property-read int|null $activities_count
 * @property-read Blueprint $blueprint
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Globals newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Globals newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Globals query()
 * @method static \Illuminate\Database\Eloquent\Builder|Globals whereBlueprintId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Globals whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Globals whereData($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Globals whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Globals whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Globals whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Globals whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
#[ OnDeleteCascade(['blueprintData']) ]
class Globals extends Model implements HasInternationalizationInterface
{
    use ConstraintsRelationships;
    use HasSlug;
    use LogsActivity;
    use Sites;

    protected $table = 'globals';

    /**
     * Declare columns
     * that are mass assignable.
     */
    protected $fillable = [
        'name',
        'blueprint_id',
        'slug',
        'data',
        'locale',
        'translation_id',
    ];

    protected function casts(): array
    {
        return [
            'data' => 'array',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    /**
     * Declare relationship of
     * current model to blueprint.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\Domain\Blueprint\Models\Blueprint, $this>
     */
    public function blueprint(): BelongsTo
    {
        return $this->belongsTo(Blueprint::class);
    }

    /** @return \Illuminate\Database\Eloquent\Relations\MorphMany<\Domain\Blueprint\Models\BlueprintData, $this> */
    public function blueprintData(): MorphMany
    {
        return $this->morphMany(BlueprintData::class, 'model');
    }

    /**
     * Set the column reference
     * for route keys.
     */
    #[\Override]
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name')
            ->preventOverwrite()
            ->doNotGenerateSlugsOnUpdate()
            ->saveSlugsTo($this->getRouteKeyName());
    }

    /** @return \Illuminate\Database\Eloquent\Relations\HasMany<\Domain\Globals\Models\Globals, $this> */
    public function dataTranslation(): HasMany
    {
        return $this->hasMany(self::class, 'translation_id');
    }

    /** @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\Domain\Globals\Models\Globals, $this> */
    public function parentTranslation(): BelongsTo
    {
        return $this->belongsTo(self::class, 'translation_id');
    }
}
