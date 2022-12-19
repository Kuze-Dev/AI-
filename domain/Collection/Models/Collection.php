<?php

declare(strict_types=1);

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
     * Declare columns 
     * that are mass assignable.
     */
    protected $fillable = [
        'name',
        'blueprint_id',
        'slug',
        'past_publish_date',
        'future_publish_date',
        'is_sortable',
    ];

    /**
     * Columns that are converted 
     * to a specific data type.
     */
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
     * Declare relationship of 
     * current model to blueprint.
     */
    public function blueprint(): BelongsTo
    {
        return $this->belongsTo(Blueprint::class);
    }

    /**
     * Declare relationship of
     * current model to collection entries.
     */
    public function collectionEntries(): HasMany
    {
        return $this->hasMany(CollectionEntry::class);
    }

    /**
     * Specify activity log description.
     */
    public function getActivitySubjectDescription(Activity $activity): string
    {
        return 'Collection: '.$this->name;
    }

    /**
     * Set the column refrence 
     * for route keys.
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

    /**
     * Check if date behaviors has values.
     */
    public function hasPublishDates(): bool
    {
        return $this->past_publish_date || $this->future_publish_date;
    }
}