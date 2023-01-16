<?php

declare(strict_types=1);

namespace Domain\Collection\Models;

use AlexJustesen\FilamentSpatieLaravelActivitylog\Contracts\IsActivitySubject;
use Domain\Blueprint\Models\Blueprint;
use Domain\Taxonomy\Models\Taxonomy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;
use Domain\Collection\Enums\PublishBehavior;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Domain\Collection\Models\Collection
 *
 * @property bool $is_sortable
 */
class Collection extends Model implements IsActivitySubject
{
    use LogsActivity;
    use HasSlug;

    /**
     * Declare columns
     * that are mass assignable.
     */
    protected $fillable = [
        'name',
        'blueprint_id',
        'taxonomy_id',
        'slug',
        'past_publish_date_behavior',
        'future_publish_date_behavior',
        'is_sortable',
    ];

    /**
     * Columns that are converted
     * to a specific data type.
     */
    protected $casts = [
        'data' => 'array',
        'is_sortable' => 'boolean',
        'past_publish_date_behavior' => PublishBehavior::class,
        'future_publish_date_behavior' => PublishBehavior::class,
    ];

    /** @return LogOptions */
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
      *
      * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\Domain\Blueprint\Models\Blueprint, \Domain\Collection\Models\Collection>
      */
    public function blueprint(): BelongsTo
    {
        return $this->belongsTo(Blueprint::class);
    }

    /**
     * Declare relationship of
     * current model to collection entries.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\Domain\Collection\Models\CollectionEntry>
     */
    public function collectionEntries(): HasMany
    {
        return $this->hasMany(CollectionEntry::class);
    }

    /**
     * Declare relationship of
     * current model to taxonomy.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany<\Domain\Taxonomy\Models\Taxonomy>
     */
    public function taxonomies(): BelongsToMany
    {
        return $this->belongsToMany(
            Taxonomy::class,
            'collection_taxonomies',
            'collection_id',
            'taxonomy_id',
        );
    }

    /** Specify activity log description. */
    public function getActivitySubjectDescription(Activity $activity): string
    {
        return 'Collection: '.$this->name;
    }

    /**
     * Set the column reference
     * for route keys.
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /** @return SlugOptions */
    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name')
            ->preventOverwrite()
            ->doNotGenerateSlugsOnUpdate()
            ->saveSlugsTo($this->getRouteKeyName());
    }

    /** Check if date behaviors has values. */
    public function hasPublishDates(): bool
    {
        return $this->past_publish_date_behavior || $this->future_publish_date_behavior;
    }
}
