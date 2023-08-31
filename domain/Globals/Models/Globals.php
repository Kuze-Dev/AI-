<?php

declare(strict_types=1);

namespace Domain\Globals\Models;

use Domain\Site\Traits\Sites;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;
use Spatie\Activitylog\LogOptions;
use Domain\Blueprint\Models\Blueprint;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Support\ConstraintsRelationships\ConstraintsRelationships;

/**
 * Domain\Globals\Models\Globals
 *
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string $blueprint_id
 * @property array|null $data
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
 * @property-read int|null $activities_count
 * @property-read Blueprint $blueprint
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
 * @mixin \Eloquent
 */
class Globals extends Model
{
    use LogsActivity;
    use HasSlug;
    use ConstraintsRelationships;
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
    ];

    protected $casts = [
        'data' => 'array',
    ];

    /** @return LogOptions */
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
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\Domain\Blueprint\Models\Blueprint, \Domain\Globals\Models\Globals>
     */
    public function blueprint(): BelongsTo
    {
        return $this->belongsTo(Blueprint::class);
    }

    /**
     * Set the column reference
     * for route keys.
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /** @return SlugOptions */
    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name')
            ->preventOverwrite()
            ->doNotGenerateSlugsOnUpdate()
            ->saveSlugsTo($this->getRouteKeyName());
    }
}
