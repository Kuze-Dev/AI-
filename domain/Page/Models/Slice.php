<?php

declare(strict_types=1);

namespace Domain\Page\Models;

use AlexJustesen\FilamentSpatieLaravelActivitylog\Contracts\IsActivitySubject;
use Domain\Blueprint\Models\Blueprint;
use Domain\Support\ConstraintsRelationships\Attributes\OnDeleteRestrict;
use Domain\Support\ConstraintsRelationships\ConstraintsRelationships;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * Domain\Page\Models\Slice
 *
 * @property int $id
 * @property string $blueprint_id
 * @property string $name
 * @property string $component
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|Activity[] $activities
 * @property-read int|null $activities_count
 * @property-read Blueprint $blueprint
 * @method static \Illuminate\Database\Eloquent\Builder|Slice newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Slice newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Slice query()
 * @method static \Illuminate\Database\Eloquent\Builder|Slice whereBlueprintId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Slice whereComponent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Slice whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Slice whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Slice whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Slice whereUpdatedAt($value)
 * @mixin \Eloquent
 */
#[OnDeleteRestrict(['sliceContents'])]
class Slice extends Model implements IsActivitySubject
{
    use LogsActivity;
    use ConstraintsRelationships;

    protected $fillable = [
        'blueprint_id',
        'name',
        'component',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    /** @return BelongsTo<Blueprint, Slice> */
    public function blueprint(): BelongsTo
    {
        return $this->belongsTo(Blueprint::class);
    }

    /** @return HasMany<SliceContent> */
    public function sliceContents(): HasMany
    {
        return $this->hasMany(SliceContent::class);
    }

    public function getActivitySubjectDescription(Activity $activity): string
    {
        return 'Slice: '.$this->name;
    }
}
