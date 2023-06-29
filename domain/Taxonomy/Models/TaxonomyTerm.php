<?php

declare(strict_types=1);

namespace Domain\Taxonomy\Models;

use Domain\Content\Models\ContentEntry;
use Support\ConstraintsRelationships\Attributes\OnDeleteCascade;
use Support\ConstraintsRelationships\ConstraintsRelationships;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;
use Illuminate\Database\Eloquent\Builder;

/**
 * Domain\Taxonomy\Models\TaxonomyTerm
 *
 * @property-read \Illuminate\Database\Eloquent\Collection<int, TaxonomyTerm> $children
 * @property-read int|null $children_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, ContentEntry> $contentEntries
 * @property-read int|null $content_entries_count
 * @property-read \Domain\Taxonomy\Models\Taxonomy|null $taxonomy
 * @method static Builder|TaxonomyTerm newModelQuery()
 * @method static Builder|TaxonomyTerm newQuery()
 * @method static Builder|TaxonomyTerm ordered(string $direction = 'asc')
 * @method static Builder|TaxonomyTerm query()
 * @mixin \Eloquent
 */
#[OnDeleteCascade(['contentEntries', 'children'])]
class TaxonomyTerm extends Model implements Sortable
{
    use HasSlug;
    use SortableTrait;
    use ConstraintsRelationships;

    protected $fillable = [
        'taxonomy_id',
        'parent_id',
        'name',
        'slug',
        'data',
        'order',
    ];

    protected $casts = ['data' => 'array'];

    /** @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\Domain\Taxonomy\Models\Taxonomy, \Domain\Taxonomy\Models\TaxonomyTerm> */
    public function taxonomy(): BelongsTo
    {
        return $this->belongsTo(Taxonomy::class);
    }

    /** @return HasMany<TaxonomyTerm> */
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->ordered()->with('children');
    }

    /**
     * Declare relationship of
     * current model to content entries.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany<\Domain\Content\Models\ContentEntry>
     */
    public function contentEntries(): BelongsToMany
    {
        return $this->belongsToMany(ContentEntry::class);
    }

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

    /** @return Builder<TaxonomyTerm> */
    public function buildSortQuery(): Builder
    {
        return static::query()->whereTaxonomyId($this->taxonomy_id)->whereParentId($this->parent_id);
    }
}
