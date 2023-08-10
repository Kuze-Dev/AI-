<?php

declare(strict_types=1);

namespace Domain\Product\Models;

use Support\ConstraintsRelationships\ConstraintsRelationships;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Domain\Product\Models\ProductVariant
 *
 * @property int $id
 * @property int $product_id
 * @property string $sku
 * @property array $combination
 * @property string $retail_price
 * @property string $selling_price
 * @property int $stock
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Domain\Product\Models\Product|null $product
 * @method static \Illuminate\Database\Eloquent\Builder|ProductVariant newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProductVariant newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProductVariant query()
 * @method static \Illuminate\Database\Eloquent\Builder|ProductVariant whereCombination($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductVariant whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductVariant whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductVariant whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductVariant whereRetailPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductVariant whereSellingPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductVariant whereSku($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductVariant whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductVariant whereStock($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductVariant whereUpdatedAt($value)
 * @mixin \Eloquent
 */
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
        'status' => 'boolean',
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
