<?php

declare(strict_types=1);

namespace Domain\Taxonomy\Models;

use Domain\Collection\Models\CollectionEntry;
use Domain\Support\ConstraintsRelationships\Attributes\OnDeleteCascade;
use Domain\Support\ConstraintsRelationships\ConstraintsRelationships;
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
 * @property int $id
 * @property int $taxonomy_id
 * @property int|null $parent_id
 * @property string $name
 * @property string $slug
 * @property array $data
 * @property int $order
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|TaxonomyTerm[] $children
 * @property-read int|null $children_count
 * @property-read \Illuminate\Database\Eloquent\Collection|CollectionEntry[] $collectionEntries
 * @property-read int|null $collection_entries_count
 * @property-read \Domain\Taxonomy\Models\Taxonomy $taxonomy
 * @method static Builder|TaxonomyTerm newModelQuery()
 * @method static Builder|TaxonomyTerm newQuery()
 * @method static Builder|TaxonomyTerm ordered(string $direction = 'asc')
 * @method static Builder|TaxonomyTerm query()
 * @method static Builder|TaxonomyTerm whereCreatedAt($value)
 * @method static Builder|TaxonomyTerm whereData($value)
 * @method static Builder|TaxonomyTerm whereId($value)
 * @method static Builder|TaxonomyTerm whereName($value)
 * @method static Builder|TaxonomyTerm whereOrder($value)
 * @method static Builder|TaxonomyTerm whereParentId($value)
 * @method static Builder|TaxonomyTerm whereSlug($value)
 * @method static Builder|TaxonomyTerm whereTaxonomyId($value)
 * @method static Builder|TaxonomyTerm whereUpdatedAt($value)
 * @mixin \Eloquent
 */
#[OnDeleteCascade(['collectionEntries', 'children'])]
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
     * current model to collection entries.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany<\Domain\Collection\Models\CollectionEntry>
     */
    public function collectionEntries(): BelongsToMany
    {
        return $this->belongsToMany(CollectionEntry::class);
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
