<?php

declare(strict_types=1);

namespace Domain\Product\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;
use Support\ConstraintsRelationships\Attributes\OnDeleteCascade;
use Support\ConstraintsRelationships\ConstraintsRelationships;

/**
 * Domain\Product\Models\ProductOption
 *
 * @property int $id
 * @property int $product_id
 * @property string $name
 * @property string $slug
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Domain\Product\Models\Product $product
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Domain\Product\Models\ProductOptionValue> $productOptionValues
 * @property-read int|null $product_option_values_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder|ProductOption newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProductOption newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProductOption query()
 * @method static \Illuminate\Database\Eloquent\Builder|ProductOption whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductOption whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductOption whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductOption whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductOption whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductOption whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
#[OnDeleteCascade(['productOptionValues'])]
class ProductOption extends Model
{
    use ConstraintsRelationships;
    use HasSlug;

    protected $fillable = [
        'name',
        'product_id',
        'is_custom',
    ];

    protected $with = [
        'productOptionValues',
    ];

    /**
     * Columns that are converted
     * to a specific data type.
     */
    protected $casts = [
        'is_custom' => 'boolean',
    ];

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

    /** @return \Illuminate\Database\Eloquent\Relations\HasMany<\Domain\Product\Models\ProductOptionValue> */
    public function productOptionValues(): HasMany
    {
        return $this->hasMany(ProductOptionValue::class);
    }

    /**
     * Declare relationship of
     * current model to product.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\Domain\Product\Models\Product, \Domain\Product\Models\ProductOption>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
