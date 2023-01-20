<?php

declare(strict_types=1);

namespace Domain\Page\Models;

use AlexJustesen\FilamentSpatieLaravelActivitylog\Contracts\IsActivitySubject;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

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
