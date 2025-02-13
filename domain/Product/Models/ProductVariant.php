<?php

declare(strict_types=1);

namespace Domain\Product\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Image\Enums\Fit;
use Spatie\Image\Manipulations;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Support\ConstraintsRelationships\ConstraintsRelationships;

/**
 * Domain\Product\Models\ProductVariant
 *
 * @property int $id
 * @property int $product_id
 * @property string $sku
 * @property array $combination
 * @property string $retail_price
 * @property string $selling_price
 * @property int|null $stock
 * @property bool $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Domain\Product\Models\Product|null $product
 *
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
 *
 * @mixin \Eloquent
 */
class ProductVariant extends Model implements HasMedia
{
    use ConstraintsRelationships;
    /** @use InteractsWithMedia<\Spatie\MediaLibrary\MediaCollections\Models\Media> */
    use InteractsWithMedia;

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
    protected function casts(): array
    {
        return [
            'combination' => 'array',
            'status' => 'boolean',
        ];
    }
    /**
     * Get the stringify combination (array)
     *
     * @return Attribute<string, static>
     */
    protected function stringCombination(): Attribute
    {
        return Attribute::make(
            get: function ($value) {
                $combinationString = '';

                foreach ($this->combination as $option) {
                    $combinationString .= $option['option'].': '.$option['option_value'].' | ';
                }

                // Remove the trailing comma and space
                $combinationString = rtrim($combinationString, ' | ');

                return $combinationString;
            }
        );
    }

    /**
     * Declare relationship of
     * current model to product.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\Domain\Product\Models\Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    #[\Override]
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('image')
            ->registerMediaConversions(function () {
                $this->addMediaConversion('original');
                $this->addMediaConversion('preview')
                    ->fit(Fit::Crop, 300, 300);
            });

        $this->addMediaCollection('video')
            ->registerMediaConversions(fn () => $this->addMediaConversion('original'));
    }
}
