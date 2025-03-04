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
use Illuminate\Database\Eloquent\Relations\MorphOne;
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
use Support\RouteUrl\Models\RouteUrl;

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
 * @property string|null $translation_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, TaxonomyTerm> $children
 * @property-read int|null $children_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, ContentEntry> $contentEntries
 * @property-read int|null $content_entries_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Product> $products
 * @property-read int|null $products_count
 * @property-read \Domain\Taxonomy\Models\Taxonomy $taxonomy
 * @property-read \Support\RouteUrl\Models\RouteUrl|null $activeRouteUrl
 * @property-read \Support\RouteUrl\Models\RouteUrl|null $routeUrls
 * @property-read int|null $route_urls_count
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
 *
 * @mixin \Eloquent
 */
#[
    OnDeleteCascade(['contentEntries', 'children', 'blueprintData', 'routeUrls', 'dataTranslation']),
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
        'translation_id',
        'name',
        'slug',
        'data',
        'order',
    ];

    protected $appends = ['url'];

    protected function casts(): array
    {
        return ['data' => 'array'];
    }

    public function getUrlAttribute(): ?string
    {
        return $this->activeRouteUrl?->url ?: null;
    }

    /** @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\Domain\Taxonomy\Models\Taxonomy, $this> */
    public function taxonomy(): BelongsTo
    {
        return $this->belongsTo(Taxonomy::class);
    }

    /** @return  \Illuminate\Database\Eloquent\Relations\HasMany<\Domain\Taxonomy\Models\TaxonomyTerm, $this> */
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->ordered()->with('children');
    }

    /**
     * Declare relationship of
     * current model to content entries.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany<\Domain\Content\Models\ContentEntry, $this>
     */
    public function contentEntries(): BelongsToMany
    {
        return $this->belongsToMany(ContentEntry::class);
    }

    /**
     * Declare relationship of
     * current model to products.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany<\Domain\Product\Models\Product, $this>
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class);
    }

    /** @return \Illuminate\Database\Eloquent\Relations\BelongsToMany<\Domain\Service\Models\Service, $this> */
    public function services(): BelongsToMany
    {
        return $this->belongsToMany(Service::class, 'service_taxonomy_terms');
    }

    /** @return \Illuminate\Database\Eloquent\Relations\MorphMany<\Domain\Blueprint\Models\BlueprintData, $this> */
    public function blueprintData(): MorphMany
    {
        return $this->morphMany(BlueprintData::class, 'model');
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

    /** @return \Illuminate\Database\Eloquent\Builder<\Domain\Taxonomy\Models\TaxonomyTerm> */
    public function buildSortQuery(): Builder
    {
        /**
         * Method Domain\Taxonomy\Models\TaxonomyTerm::buildSortQuery() should return Illuminate\Database\Eloquent\Builder<Domain\Taxonomy\Models\TaxonomyTerm> but returns Illuminate\Database\Eloquent\Builder<static(Domain\Taxonomy\Models\TaxonomyTerm)>domain/Taxonomy/Models/TaxonomyTerm.php
         *  @phpstan-ignore return.type */
        return static::query()->whereTaxonomyId($this->taxonomy_id)->whereParentId($this->parent_id);
    }

    public static function generateRouteUrl(Model $model, array $attributes): string
    {
        /** @var TaxonomyTerm */
        $taxonomy = $model->load('taxonomy');

        return $taxonomy->taxonomy->activeRouteUrl?->url.'/'.Str::of($attributes['name'])->slug()->toString();
    }

    /** @return \Illuminate\Database\Eloquent\Relations\HasMany<\Domain\Taxonomy\Models\TaxonomyTerm, $this> */
    public function dataTranslation(): HasMany
    {
        return $this->hasMany(self::class, 'translation_id');
    }

    /** @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\Domain\Taxonomy\Models\TaxonomyTerm, $this> */
    public function parentTranslation(): BelongsTo
    {
        return $this->belongsTo(self::class, 'translation_id');
    }
}
