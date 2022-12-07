<?php 

declare(strict_types = 1);

namespace Domain\Collection\Models;

use AlexJustesen\FilamentSpatieLaravelActivitylog\Contracts\IsActivitySubject;
use Domain\Blueprint\Models\Blueprint;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Collection extends Model implements IsActivitySubject 
{
    use LogsActivity,
        HasSlug;

    /**
     * Declaration of constants for labels 
     * collection of publish date behaviors.
     */
    const BEHAVIOR_PUBLIC = 'public';
    
    const BEHAVIOR_UNLISTED = 'unlisted';

    const BEHAVIOR_PRIVATE = 'private';
    
    protected $fillable = [
        'name',
        'blueprint_id',
        'slug',
        'display_publish_dates',
        'past_publish_date',
        'future_publish_date',
        'data',
        'isSortable'
    ];

    protected $casts = [
        'data' => 'array',
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
     * @return BelongsTo
     */
    public function blueprint(): BelongsTo 
    {
        return $this->belongsTo(Blueprint::class);
    }

    /**
     * @return HasMany
     */
    public function collectionEntries(): HasMany 
    {
        return $this->hasMany(CollectionEntry::class);
    }

    /**
     * @param Activity $activity
     * 
     * @return string
     */
    public function getActivitySubjectDescription(Activity $activity): string
    {
        return 'Collection: '.$this->name;
    }

    /**
     * @return string
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * @return SlugOptions
     */
    public function getSlugOptions(): SlugOptions 
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name')
            ->preventOverwrite()
            ->doNotGenerateSlugsOnUpdate()
            ->saveSlugsTo($this->getRouteKeyName());
    }
}