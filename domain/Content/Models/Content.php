<?php

declare(strict_types=1);

namespace Domain\Content\Models;

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
use Domain\Content\Enums\PublishBehavior;
use Domain\Support\ConstraintsRelationships\Attributes\OnDeleteCascade;
use Domain\Support\ConstraintsRelationships\Attributes\OnDeleteRestrict;
use Domain\Support\ConstraintsRelationships\ConstraintsRelationships;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Domain\Content\Models\Content
 *
 * @property PublishBehavior $past_publish_date_behavior
 * @property PublishBehavior $future_publish_date_behavior
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
 * @property-read int|null $activities_count
 * @property-read Blueprint|null $blueprint
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Domain\Content\Models\ContentEntry> $contentEntries
 * @property-read int|null $content_entries_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Taxonomy> $taxonomies
 * @property-read int|null $taxonomies_count
 * @method static \Illuminate\Database\Eloquent\Builder|Content newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Content newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Content query()
 * @mixin \Eloquent
 */
#[
    OnDeleteCascade(['taxonomies']),
    OnDeleteRestrict(['contentEntries'])
]
class Content extends Model
{
    use LogsActivity;
    use HasSlug;
    use ConstraintsRelationships;

    /**
     * Declare columns
     * that are mass assignable.
     */
    protected $fillable = [
        'name',
        'blueprint_id',
        'slug',
        'prefix',
        'past_publish_date_behavior',
        'future_publish_date_behavior',
        'is_sortable',
    ];

    /**
     * Columns that are converted
     * to a specific data type.
     */
    protected $casts = [
        'past_publish_date_behavior' => PublishBehavior::class,
        'future_publish_date_behavior' => PublishBehavior::class,
        'is_sortable' => 'boolean',
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
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\Domain\Blueprint\Models\Blueprint, \Domain\Content\Models\Content>
     */
    public function blueprint(): BelongsTo
    {
        return $this->belongsTo(Blueprint::class);
    }

    /**
     * Declare relationship of
     * current model to content entries.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\Domain\Content\Models\ContentEntry>
     */
    public function contentEntries(): HasMany
    {
        return $this->hasMany(ContentEntry::class);
    }

    /**
     * Declare relationship of
     * current model to taxonomy.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany<\Domain\Taxonomy\Models\Taxonomy>
     */
    public function taxonomies(): BelongsToMany
    {
        return $this->belongsToMany(Taxonomy::class);
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
