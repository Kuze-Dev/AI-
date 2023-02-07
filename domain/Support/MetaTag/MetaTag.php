<?php

declare(strict_types=1);

namespace Domain\Support\MetaTag;
use AlexJustesen\FilamentSpatieLaravelActivitylog\Contracts\IsActivitySubject;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

class MetaTag extends Model implements IsActivitySubject
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
        'author',
        'keywords'
    ];

    /**
     * @return LogOptions
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    /**
     * Specify activity log description.
     */
    public function getActivitySubjectDescription(Activity $activity): string
    {
        return 'Meta tags: '.$this->title;
    }

    public function morphs()
    {
        return $this->morphTo();   
    }
}