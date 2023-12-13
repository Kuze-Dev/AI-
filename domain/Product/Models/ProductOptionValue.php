<?php

declare(strict_types=1);

namespace Domain\Product\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Image\Manipulations;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;
use Support\ConstraintsRelationships\ConstraintsRelationships;

/**
 * Domain\Product\Models\ProductOptionValue
 *
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property int $product_option_id
 * @property-read \Domain\Product\Models\ProductOption|null $productOption
 *
 * @method static \Illuminate\Database\Eloquent\Builder|ProductOptionValue newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProductOptionValue newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProductOptionValue query()
 * @method static \Illuminate\Database\Eloquent\Builder|ProductOptionValue whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductOptionValue whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductOptionValue whereProductOptionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductOptionValue whereSlug($value)
 *
 * @mixin \Eloquent
 */
class ProductOptionValue extends Model implements HasMedia
{
    use ConstraintsRelationships;
    use HasSlug;
    use InteractsWithMedia;

    public $timestamps = false;

    protected $fillable = [
        'name',
        'product_option_id',
        'data',
    ];

    protected $with = [
        'media',
    ];

    /**
     * Columns that are converted
     * to a specific data type.
     */
    protected $casts = [
        'data' => 'array',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * Get the product option name
     *
     * @return Attribute<string, static>
     */
    protected function productOptionName(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $this->productOption ? $this->productOption->name : '',
        );
    }

    /**
     * Get the icon details
     *
     * @return Attribute<string, static>
     */
    protected function iconDetails(): Attribute
    {
        return Attribute::make(
            get: function ($value) {
                if (is_null($this->data) || $this->data['icon_type'] == 'text') {
                    return 'Type: Text | Value: N/A';
                } else {
                    $iconTypeTransformed = ucwords(str_replace('_', ' ', $this->data['icon_type']));

                    return "Type: {$iconTypeTransformed} | Value: {$this->data['icon_value']}";
                }
            }
        );
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

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('media')
            ->registerMediaConversions(function () {
                $this->addMediaConversion('original');
                $this->addMediaConversion('preview')
                    ->fit(Manipulations::FIT_CROP, 300, 300);
            });
    }
}
