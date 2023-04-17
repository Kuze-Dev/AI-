<?php

declare(strict_types=1);

namespace Domain\Internationalization\Models;

use AlexJustesen\FilamentSpatieLaravelActivitylog\Contracts\IsActivitySubject;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;
use Domain\Support\ConstraintsRelationships\ConstraintsRelationships;


/**
 * Domain\Globals\Models\Globals
 *
 * @property int $id
 * @property string $locale
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|Activity[] $activities
 * @property-read int|null $activities_count
 * @method static \Illuminate\Database\Eloquent\Builder|Globals newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Globals newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Globals query()
 * @method static \Illuminate\Database\Eloquent\Builder|Globals whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Globals whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Globals whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Locale extends Model implements IsActivitySubject
{
    use LogsActivity;
    use ConstraintsRelationships;

    protected $table = 'locales';

    /**
     * Declare columns
     * that are mass assignable.
     */
    protected $fillable = [
        'locale',
        'name',
    ];

    /** @return LogOptions */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    /** Specify activity log description. */
    public function getActivitySubjectDescription(Activity $activity): string
    {
        return 'Locale: '.$this->locale;
    }

    /**
     * Set the column reference
     * for route keys.
     */
    public function getRouteKeyName(): string
    {
        return 'locale';
    }
}
