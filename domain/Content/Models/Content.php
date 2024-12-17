<?php

declare(strict_types=1);

namespace Domain\Content\Models;

use Domain\Blueprint\Models\Blueprint;
use Domain\Content\Enums\PublishBehavior;
use Domain\Site\Traits\Sites;
use Domain\Taxonomy\Models\Taxonomy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;
use Support\ConstraintsRelationships\Attributes\OnDeleteCascade;
use Support\ConstraintsRelationships\Attributes\OnDeleteRestrict;
use Support\ConstraintsRelationships\ConstraintsRelationships;

/**
 * Domain\Content\Models\Content
 *
 * @property int $id
 * @property string $blueprint_id
 * @property string $name
 * @property string $slug
 * @property string $prefix
 * @property string $visibility
 * @property PublishBehavior|null $future_publish_date_behavior
 * @property PublishBehavior|null $past_publish_date_behavior
 * @property bool $is_sortable
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
 * @property-read int|null $activities_count
 * @property-read Blueprint $blueprint
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Domain\Content\Models\ContentEntry> $contentEntries
 * @property-read int|null $content_entries_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Taxonomy> $taxonomies
 * @property-read int|null $taxonomies_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Content newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Content newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Content query()
 * @method static \Illuminate\Database\Eloquent\Builder|Content whereBlueprintId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Content whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Content whereFuturePublishDateBehavior($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Content whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Content whereIsSortable($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Content whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Content wherePastPublishDateBehavior($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Content wherePrefix($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Content whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Content whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
#[
    OnDeleteCascade(['taxonomies']),
    OnDeleteRestrict(['contentEntries'])
]
class Content extends Model
{
    use ConstraintsRelationships;
    use HasSlug;
    use LogsActivity;
    use Sites;

    /**
     * Declare columns
     * that are mass assignable.
     */
    protected $fillable = [
        'name',
        'blueprint_id',
        'slug',
        'prefix',
        'visibility',
        'past_publish_date_behavior',
        'future_publish_date_behavior',
        'is_sortable',
    ];

    /**
     * Columns that are converted
     * to a specific data type.
     */
    protected function casts(): array
    {
        return [
            'past_publish_date_behavior' => PublishBehavior::class,
            'future_publish_date_behavior' => PublishBehavior::class,
            'is_sortable' => 'boolean',
        ];
    }

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
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\Domain\Blueprint\Models\Blueprint, $this>
     */
    public function blueprint(): BelongsTo
    {
        return $this->belongsTo(Blueprint::class);
    }

    /**
     * Declare relationship of
     * current model to content entries.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\Domain\Content\Models\ContentEntry, $this>
     */
    public function contentEntries(): HasMany
    {
        return $this->hasMany(ContentEntry::class);
    }

    /**
     * Declare relationship of
     * current model to taxonomy.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany<\Domain\Taxonomy\Models\Taxonomy, $this>
     */
    public function taxonomies(): BelongsToMany
    {
        return $this->belongsToMany(Taxonomy::class);
    }

    /**
     * Set the column reference
     * for route keys.
     */
    #[\Override]
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

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
