<?php

declare(strict_types=1);

namespace Domain\Support\MetaData\Models;

use AlexJustesen\FilamentSpatieLaravelActivitylog\Contracts\IsActivitySubject;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * @property-read \Illuminate\Database\Eloquent\Collection|MetaData[] $metaData
 */
class MetaData extends Model implements IsActivitySubject
{
    use LogsActivity;

    /**
     * Declare columns
     * that are mass assignable.
     */
    protected $fillable = [
        'title',
        'author',
        'description',
        'keywords',
    ];

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
        return 'Meta tags: '.$this->title;
    }

    /** @return MorphTo<Model, self> */
    public function model(): MorphTo
    {
        return $this->morphTo();
    }
}
