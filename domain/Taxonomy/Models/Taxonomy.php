<?php

declare(strict_types=1);

namespace Domain\Taxonomy\Models;

use Domain\Blueprint\Models\Blueprint;
use Domain\Content\Models\Content;
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
 * Domain\Taxonomy\Models\Taxonomy
 *
 * @property int $id
 * @property string $blueprint_id
 * @property string $name
 * @property string $slug
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
 * @property-read int|null $activities_count
 * @property-read Blueprint $blueprint
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Content> $contents
 * @property-read int|null $contents_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Domain\Taxonomy\Models\TaxonomyTerm> $parentTerms
 * @property-read int|null $parent_terms_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Domain\Taxonomy\Models\TaxonomyTerm> $taxonomyTerms
 * @property-read int|null $taxonomy_terms_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Taxonomy newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Taxonomy newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Taxonomy query()
 * @method static \Illuminate\Database\Eloquent\Builder|Taxonomy whereBlueprintId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Taxonomy whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Taxonomy whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Taxonomy whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Taxonomy whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Taxonomy whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
#[
    OnDeleteCascade(['taxonomyTerms']),
    OnDeleteRestrict(['contents'])
]
class Taxonomy extends Model
{
    use ConstraintsRelationships;
    use HasSlug;
    use LogsActivity;

    protected $fillable = [
        'name',
        'slug',
        'blueprint_id',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    /** @return BelongsTo<Blueprint, Taxonomy> */
    public function blueprint(): BelongsTo
    {
        return $this->belongsTo(Blueprint::class);
    }

    /** @return HasMany<TaxonomyTerm> */
    public function parentTerms(): HasMany
    {
        return $this->taxonomyTerms()->whereNull('parent_id')->ordered();
    }

    /** @return \Illuminate\Database\Eloquent\Relations\HasMany<\Domain\Taxonomy\Models\TaxonomyTerm> */
    public function taxonomyTerms(): HasMany
    {
        return $this->hasMany(TaxonomyTerm::class);
    }

    /**
     * Declare relationship of
     * current model to contents.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany<\Domain\Content\Models\Content>
     */
    public function contents(): BelongsToMany
    {
        return $this->belongsToMany(Content::class);
    }

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
}
