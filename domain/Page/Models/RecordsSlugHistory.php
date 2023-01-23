<?php

declare(strict_types=1);

namespace Domain\Page\Models;

use AlexJustesen\FilamentSpatieLaravelActivitylog\Contracts\IsActivitySubject;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;
use Eloquent;

/**
 * Domain\Page\Models\RecordsSlugHistory
 * @property int $id
 * @property int $sluggable_id
 * @property string $slug
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|Activity[] $activities
 * @property-read int|null $activities_count
 * @property-read Model|Eloquent $sluggable
 * @method static \Illuminate\Database\Eloquent\Builder|RecordsSlugHistory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RecordsSlugHistory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RecordsSlugHistory query()
 * @mixin \Eloquent
 */
class RecordsSlugHistory extends Model implements IsActivitySubject
{
    use LogsActivity;

    protected $fillable = [
        'slug',
        'sluggable_id',
        'sluggable_type',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    /** @return MorphTo<Model&RecordsSlugHistory, self> */
    public function sluggable(): MorphTo
    {
        /** @var MorphTo<Model&RecordsSlugHistory, self> */
        return $this->morphTo();
    }

    public function getActivitySubjectDescription(Activity $activity): string
    {
        return 'RecordsSlugHistory: '.$this->slug;
    }
}
