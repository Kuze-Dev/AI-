<?php

declare(strict_types=1);

namespace Domain\Product\Models;

use Support\ConstraintsRelationships\ConstraintsRelationships;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductVariant extends Model
{
    use ConstraintsRelationships;

    protected $fillable = [
        'product_id',
        'sku',
        'combination',
        'retail_price',
        'selling_price',
        'stock',
        'status',
    ];

    /**
     * Columns that are converted
     * to a specific data type.
     */
    protected $casts = [
        'combination' => 'array',
    ];

    /**
     * Declare relationship of
     * current model to product.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\Domain\Product\Models\Product, \Domain\Product\Models\ProductVariant>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
