<?php

declare(strict_types=1);

namespace Domain\Product\Models;

use Support\ConstraintsRelationships\ConstraintsRelationships;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class ProductOption extends Model
{
    use HasSlug;
    use ConstraintsRelationships;

    protected $fillable = [
        'name',
        'product_id',
    ];

    /**
     * Columns that are converted
     * to a specific data type.
     */
    protected $casts = [];

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
