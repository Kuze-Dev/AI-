<?php

declare(strict_types=1);

namespace Domain\Product\Models;

use Domain\Support\ConstraintsRelationships\ConstraintsRelationships;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class ProductOptionValue extends Model
{
    use HasSlug;
    use ConstraintsRelationships;

    public $timestamps = false;

    protected $fillable = [
        'product_option_id',
        'name',
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

    /**
     * Declare relationship of
     * current model to product option.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\Domain\Product\Models\ProductOption, \Domain\Product\Models\ProductOptionValue>
     */
    public function productOption(): BelongsTo
    {
        return $this->belongsTo(ProductOption::class);
    }
}
