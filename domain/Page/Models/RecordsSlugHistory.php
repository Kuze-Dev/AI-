<?php

declare(strict_types=1);

namespace Domain\Page\Models;

use AlexJustesen\FilamentSpatieLaravelActivitylog\Contracts\IsActivitySubject;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * Domain\Page\Models\RecordsSlugHistory
 *
 * @property int $id
 * @property string $slug
 * @property int $sluggable_id
 * @property string $sluggable_type
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read MorphTo $sluggable
 * @method static \Illuminate\Database\Eloquent\Builder|RecordsSlugHistory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RecordsSlugHistory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RecordsSlugHistory query()
 * @method static \Illuminate\Database\Eloquent\Builder|RecordsSlugHistory whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecordsSlugHistory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecordsSlugHistory whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecordsSlugHistory whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecordsSlugHistory whereUpdatedAt($value)
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

    public function sluggable(): MorphTo
    {
        return $this->morphTo();
    }

    public function getActivitySubjectDescription(Activity $activity): string
    {
        return 'RecordsSlugHistory: '.$this->name;
    }
}
