<?php

declare(strict_types=1);

namespace Domain\Product\Models;

use Domain\Support\ConstraintsRelationships\Attributes\OnDeleteCascade;
use Domain\Support\MetaData\HasMetaData;
use Domain\Support\ConstraintsRelationships\ConstraintsRelationships;
use Domain\Support\RouteUrl\Contracts\HasRouteUrl as HasRouteUrlContact;
use Domain\Support\RouteUrl\HasRouteUrl;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;
use Domain\Support\MetaData\Contracts\HasMetaData as HasMetaDataContract;
use Domain\Taxonomy\Models\Taxonomy;
use Domain\Taxonomy\Models\TaxonomyTerm;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/**
 * Domain\Product\Models\Product
 *
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string $sku
 * @property string $retail_price
 * @property string $selling_price
 * @property int $stock
 * @property string|null $description
 * @property string $status
 * @property int $is_digital_product
 * @property int $is_featured
 * @property int $is_special_offer
 * @property int $allow_customer_remarks
 * @property int $allow_remark_with_image
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Domain\Support\RouteUrl\Models\RouteUrl|null $activeRouteUrl
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Activitylog\Models\Activity> $activities
 * @property-read int|null $activities_count
 * @property-read \Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection<int, \Spatie\MediaLibrary\MediaCollections\Models\Media> $media
 * @property-read int|null $media_count
 * @property-read \Domain\Support\MetaData\Models\MetaData|null $metaData
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Domain\Support\RouteUrl\Models\RouteUrl> $routeUrls
 * @property-read int|null $route_urls_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Taxonomy> $taxonomies
 * @property-read int|null $taxonomies_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, TaxonomyTerm> $taxonomyTerms
 * @property-read int|null $taxonomy_terms_count
 * @method static \Illuminate\Database\Eloquent\Builder|Product newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Product newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Product query()
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereAllowCustomerRemarks($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereAllowRemarkWithImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereIsDigitalProduct($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereIsFeatured($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereIsSpecialOffer($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereRetailPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereSellingPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereSku($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereStock($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereUpdatedAt($value)
 * @mixin \Eloquent
 */
#[OnDeleteCascade(['metaData'])]
class Product extends Model implements HasMetaDataContract, HasRouteUrlContact, HasMedia
{
    use LogsActivity;
    use HasSlug;
    use HasRouteUrl;
    use HasMetaData;
    use ConstraintsRelationships;
    use InteractsWithMedia;

    protected $fillable = [
        'name',
        'sku',
        'description',
        'retail_price',
        'selling_price',
        'stock',
        'status',
        'is_digital_product',
        'is_featured',
        'is_special_offer',
        'allow_customer_remarks',
        'allow_remark_with_image',
    ];

    /**
     * Columns that are converted
     * to a specific data type.
     */
    protected $casts = [
        'dimension' => 'array',
    ];

    /**
     * Define default reference
     * for meta data properties.
     *
     * @return array
     */
    public function defaultMetaData(): array
    {
        return [
            'title' => $this->name,
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * Declare relationship of
     * current model to taxonomy terms.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany<\Domain\Taxonomy\Models\TaxonomyTerm>
     */
    public function taxonomyTerms(): BelongsToMany
    {
        return $this->belongsToMany(TaxonomyTerm::class);
    }

    /** @return \Illuminate\Database\Eloquent\Relations\HasMany<\Domain\Product\Models\ProductVariant> */
    public function productVariants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    /** @return \Illuminate\Database\Eloquent\Relations\HasMany<\Domain\Product\Models\ProductOption> */
    public function productOptions(): HasMany
    {
        return $this->hasMany(ProductOption::class);
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name')
            ->preventOverwrite()
            ->doNotGenerateSlugsOnUpdate()
            ->saveSlugsTo($this->getRouteKeyName());
    }

    public static function generateRouteUrl(Model $model, array $attributes): string
    {
        return Str::of($attributes['name'])->slug()->start('/')->toString();
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('image')
            ->singleFile();
    }
}
