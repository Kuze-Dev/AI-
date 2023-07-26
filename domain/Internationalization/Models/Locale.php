<?php

declare(strict_types=1);

namespace Domain\Internationalization\Models;

use Spatie\Activitylog\LogOptions;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;
use Support\ConstraintsRelationships\ConstraintsRelationships;

/**
 * Domain\Globals\Models\Globals
 *
 * @property int $id
 * @property string $code
 * @property string $name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|Activity[] $activities
 * @property-read int|null $activities_count
 * @method static \Illuminate\Database\Eloquent\Builder|Locale newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Locale newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Locale query()
 * @method static \Illuminate\Database\Eloquent\Builder|Locale whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Locale whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Locale whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Locale whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Locale extends Model
{
    use LogsActivity;
    use ConstraintsRelationships;

    protected $table = 'locales';

    /**
     * Declare columns
     * that are mass assignable.
     */
    protected $fillable = [
        'code',
        'name',
        'is_default',
    ];

    protected static function booted()
    {
        static::saving(function ($locale) {
            if ($locale->is_default) {
                static::where('is_default', true)->where('id', '!=', $locale->id)->update(['is_default' => false]);
            }

            Cache::forget('locale');
        });
    }

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
        return 'Code: '.$this->code;
    }

    /**
     * Set the column reference
     * for route keys.
     */
    public function getRouteKeyName(): string
    {
        return 'code';
    }
}
