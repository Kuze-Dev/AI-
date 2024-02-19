<?php

declare(strict_types=1);

namespace Domain\Product\Models;

use Domain\Favorite\Models\Favorite;
use Domain\Product\Models\Builders\ProductBuilder;
use Domain\Review\Models\Review;
use Domain\Taxonomy\Models\TaxonomyTerm;
use Domain\Tier\Models\Tier;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Support\Str;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Image\Manipulations;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;
use Support\ConstraintsRelationships\Attributes\OnDeleteCascade;
use Support\ConstraintsRelationships\ConstraintsRelationships;
use Support\MetaData\Contracts\HasMetaData as HasMetaDataContract;
use Support\MetaData\HasMetaData;

/**
 * Domain\Product\Models\Product
 *
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string $sku
 * @property string $retail_price
 * @property string $selling_price
 * @property array|null $dimension
 * @property string|null $weight
 * @property int|null $stock
 * @property string|null $description
 * @property bool $status
 * @property bool $is_digital_product
 * @property bool $is_featured
 * @property bool $is_special_offer
 * @property bool $allow_customer_remarks
 * @property bool $allow_stocks
 * @property bool $allow_guest_purchase
 * @property int $minimum_order_quantity
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Activitylog\Models\Activity> $activities
 * @property-read int|null $activities_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Favorite> $favorites
 * @property-read int|null $favorites_count
 * @property-read \Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection<int, \Spatie\MediaLibrary\MediaCollections\Models\Media> $media
 * @property-read int|null $media_count
 * @property-read \Support\MetaData\Models\MetaData|null $metaData
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Domain\Product\Models\ProductOption> $productOptions
 * @property-read int|null $product_options_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Domain\Product\Models\ProductVariant> $productVariants
 * @property-read int|null $product_variants_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Review> $reviews
 * @property-read int|null $reviews_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, TaxonomyTerm> $taxonomyTerms
 * @property-read int|null $taxonomy_terms_count
 *
 * @method static ProductBuilder|Product newModelQuery()
 * @method static ProductBuilder|Product newQuery()
 * @method static ProductBuilder|Product query()
 * @method static ProductBuilder|Product whereAllowCustomerRemarks($value)
 * @method static ProductBuilder|Product whereAllowStocks($value)
 * @method static ProductBuilder|Product whereAllowGuestPurchase($value)
 * @method static ProductBuilder|Product whereCreatedAt($value)
 * @method static ProductBuilder|Product whereDescription($value)
 * @method static ProductBuilder|Product whereDimension($value)
 * @method static ProductBuilder|Product whereId($value)
 * @method static ProductBuilder|Product whereIsDigitalProduct($value)
 * @method static ProductBuilder|Product whereIsFeatured($value)
 * @method static ProductBuilder|Product whereIsSpecialOffer($value)
 * @method static ProductBuilder|Product whereMinimumOrderQuantity($value)
 * @method static ProductBuilder|Product whereName($value)
 * @method static ProductBuilder|Product whereRetailPrice($value)
 * @method static ProductBuilder|Product whereSellingPrice($value)
 * @method static ProductBuilder|Product whereSku($value)
 * @method static ProductBuilder|Product whereSlug($value)
 * @method static ProductBuilder|Product whereStatus($value)
 * @method static ProductBuilder|Product whereStock($value)
 * @method static ProductBuilder|Product whereTaxonomyTerms(string $taxonomy, array $terms)
 * @method static ProductBuilder|Product whereUpdatedAt($value)
 * @method static ProductBuilder|Product whereWeight($value)
 *
 * @mixin \Eloquent
 */
#[OnDeleteCascade(['metaData', 'productOptions', 'productVariants'])]
class Product extends Model implements HasMedia, HasMetaDataContract
{
    use ConstraintsRelationships;
    use HasMetaData;
    use HasSlug;
    use InteractsWithMedia;
    use LogsActivity;

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
        'allow_stocks',
        'allow_guest_purchase',
        'weight',
        'dimension',
        'minimum_order_quantity',
    ];

    /**
     * Columns that are converted
     * to a specific data type.
     */
    protected $casts = [
        'dimension' => 'array',
        'status' => 'boolean',
        'is_digital_product' => 'boolean',
        'is_featured' => 'boolean',
        'is_special_offer' => 'boolean',
        'allow_customer_remarks' => 'boolean',
        'allow_stocks' => 'boolean',
        'allow_guest_purchase' => 'boolean',
    ];

    /**
     * Define default reference
     * for meta data properties.
     */
    public function defaultMetaData(): array
    {
        return [
            'title' => $this->name,
        ];
    }

    /** @return ProductBuilder<self> */
    public function newEloquentBuilder($query): ProductBuilder
    {
        return new ProductBuilder($query);
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

    /**
     * Declare relationship of
     * current model to tiers.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany<\Domain\Tier\Models\Tier>
     */
    public function tiers(): BelongsToMany
    {
        return $this->belongsToMany(Tier::class);
    }

    /**
     * Get the product tier relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany<\Domain\Tier\Models\Tier>
     */
    public function productTier(): BelongsToMany
    {
        return $this->belongsToMany(Tier::class, 'product_tier', 'product_id', 'tier_id')
            ->withPivot(['id', 'tier_id', 'discount', 'discount_amount_type']);
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

    /**
     * Get all of the option values of product option.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasManyThrough<\Domain\Product\Models\ProductOptionValue>
     */
    public function productOptionValues(): HasManyThrough
    {
        return $this->hasManyThrough(ProductOptionValue::class, ProductOption::class);
    }

    /** @return \Illuminate\Database\Eloquent\Relations\HasMany<\Domain\Favorite\Models\Favorite> */
    public function favorites(): HasMany
    {
        return $this->hasMany(Favorite::class);
    }

    /** @return \Illuminate\Database\Eloquent\Relations\HasMany<\Domain\Review\Models\Review> */
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function isFavorite(): bool
    {
        if (! auth()->check()) {
            return false;
        }

        $customer = auth()->user();

        if ($customer) {
            return $this->favorites()->where('customer_id', $customer->id)->exists();
        }

        return false;
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
            ->useFallbackUrl('https://via.placeholder.com/500x300/333333/fff?text=No+preview+available')
            ->registerMediaConversions(function () {
                $this->addMediaConversion('original');
                $this->addMediaConversion('preview')
                    ->fit(Manipulations::FIT_CROP, 300, 300);
            });

        $this->addMediaCollection('video')
            ->useFallbackUrl('https://via.placeholder.com/500x300/333333/fff?text=No+preview+available')
            ->registerMediaConversions(fn () => $this->addMediaConversion('original'));
    }
}
