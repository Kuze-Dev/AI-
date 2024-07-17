<?php

declare(strict_types=1);

namespace Domain\Taxonomy\Models;

use Domain\Blueprint\Models\BlueprintData;
use Domain\Content\Models\ContentEntry;
use Domain\Product\Models\Product;
use Domain\Service\Models\Service;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Str;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;
use Support\ConstraintsRelationships\Attributes\OnDeleteCascade;
use Support\ConstraintsRelationships\Attributes\OnDeleteRestrict;
use Support\ConstraintsRelationships\ConstraintsRelationships;
use Support\RouteUrl\Contracts\HasRouteUrl as HasRouteUrlContract;
use Support\RouteUrl\HasRouteUrl;

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
 * @property-read \Illuminate\Database\Eloquent\Collection<int, TaxonomyTerm> $children
 * @property-read int|null $children_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, ContentEntry> $contentEntries
 * @property-read int|null $content_entries_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Product> $products
 * @property-read int|null $products_count
 * @property-read \Domain\Taxonomy\Models\Taxonomy $taxonomy
 *
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
 *
 * @mixin \Eloquent
 */
#[
    OnDeleteCascade(['contentEntries', 'children']),
    OnDeleteRestrict(['products'])
]
class TaxonomyTerm extends Model implements HasRouteUrlContract, Sortable
{
    use ConstraintsRelationships;
    use HasRouteUrl;
    use HasSlug;
    use SortableTrait;

    protected $fillable = [
        'taxonomy_id',
        'parent_id',
        'name',
        'slug',
        'data',
        'order',
    ];

    protected $casts = ['data' => 'array'];

    protected $appends = ['url'];

    public function getUrlAttribute(): ?string
    {
        return $this->activeRouteUrl?->url ?: null;
    }

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

    /**
     * Declare relationship of
     * current model to products.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany<\Domain\Product\Models\Product>
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class);
    }

    /** @return BelongsToMany<\Domain\Service\Models\Service> */
    public function services(): BelongsToMany
    {
        return $this->belongsToMany(Service::class, 'service_taxonomy_terms');
    }

    /** @return MorphMany<BlueprintData> */
    public function blueprintData(): MorphMany
    {
        return $this->morphMany(BlueprintData::class, 'model');
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

    public static function generateRouteUrl(Model $model, array $attributes): string
    {
        return Str::of($attributes['name'])->slug()->start('/')->toString();
    }
}
