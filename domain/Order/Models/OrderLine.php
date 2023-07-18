<?php

declare(strict_types=1);

namespace Domain\Order\Models;

use Domain\Review\Models\Review;
use Domain\Taxation\Enums\PriceDisplay;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * Domain\Order\Models\OrderLine
 *
 * @property int $id
 * @property int $order_id
 * @property int $purchasable_id
 * @property string $purchasable_type
 * @property string $purchasable_sku
 * @property string $name
 * @property float $unit_price
 * @property int $quantity
 * @property float $tax_total
 * @property float $tax_percentage
 * @property PriceDisplay $tax_display
 * @property float $sub_total
 * @property float $discount_total
 * @property int|null $discount_id
 * @property string|null $discount_code
 * @property float $total
 * @property array|null $remarks_data
 * @property array|null $purchasable_data
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Activitylog\Models\Activity> $activities
 * @property-read int|null $activities_count
 * @property-read \Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection<int, Media> $media
 * @property-read int|null $media_count
 * @property-read \Domain\Order\Models\Order|null $order
 * @method static \Illuminate\Database\Eloquent\Builder|OrderLine newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|OrderLine newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|OrderLine query()
 * @method static \Illuminate\Database\Eloquent\Builder|OrderLine whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderLine whereDiscountCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderLine whereDiscountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderLine whereDiscountTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderLine whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderLine whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderLine whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderLine wherePurchasableData($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderLine wherePurchasableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderLine wherePurchasableSku($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderLine wherePurchasableType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderLine whereQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderLine whereRemarksData($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderLine whereSubTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderLine whereTaxDisplay($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderLine whereTaxPercentage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderLine whereTaxTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderLine whereTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderLine whereUnitPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderLine whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class OrderLine extends Model implements HasMedia
{
    use LogsActivity;
    use InteractsWithMedia;

    protected $fillable = [
        'order_id',
        'purchasable_id',
        'purchasable_type',
        'purchasable_sku',
        'name',
        'unit_price',
        'quantity',
        'tax_total',
        'tax_display',
        'tax_percentage',
        'sub_total',
        'discount_total',
        'discount_id',
        'discount_code',
        'total',
        'remarks_data',
        'purchasable_data',
    ];

    protected $casts = [
        'unit_price' => 'float',
        'quantity' => 'integer',
        'tax_total' => 'float',
        'tax_display' => PriceDisplay::class,
        'tax_percentage' => 'float',
        'sub_total' => 'float',
        'discount_total' => 'float',
        'total' => 'float',
        'remarks_data' => 'array',
        'purchasable_data' => 'array',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function review()
    {
        return $this->hasOne(Review::class);
    }

    public function reviewDetails()
    {
        $reviews = $this->review()->get();

        return $reviews;
    }
    
    public function registerMediaCollections(): void
    {
        $registerMediaConversions = function (Media $media) {
            $this->addMediaConversion('preview');
        };

        $this->addMediaCollection('order_line_images')
            ->onlyKeepLatest(5)
            ->registerMediaConversions($registerMediaConversions);

        $this->addMediaCollection('order_line_notes')
            ->onlyKeepLatest(5)
            ->registerMediaConversions($registerMediaConversions);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
