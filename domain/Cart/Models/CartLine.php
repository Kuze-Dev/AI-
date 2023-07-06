<?php

declare(strict_types=1);

namespace Domain\Cart\Models;

use Domain\Product\Models\ProductVariant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * Domain\Cart\Models\CartLine
 *
 * @property int $id
 * @property int $cart_id
 * @property int $purchasable_id
 * @property string $purchasable_type
 * @property int|null $variant_id
 * @property int $quantity
 * @property string|null $notes
 * @property \Illuminate\Support\Carbon|null $checked_out_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Domain\Cart\Models\Cart|null $cart
 * @property-read \Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection<int, Media> $media
 * @property-read int|null $media_count
 * @property-read Model|\Eloquent $purchasable
 * @property-read ProductVariant|null $variant
 * @method static \Illuminate\Database\Eloquent\Builder|CartLine newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CartLine newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CartLine query()
 * @method static \Illuminate\Database\Eloquent\Builder|CartLine whereCartId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CartLine whereCheckedOutAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CartLine whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CartLine whereForCheckOut($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CartLine whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CartLine whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CartLine wherePurchasableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CartLine wherePurchasableType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CartLine whereQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CartLine whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CartLine whereVariantId($value)
 * @mixin \Eloquent
 */
class CartLine extends Model implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia;

    protected $fillable = [
        'cart_id',
        'purchasable_id',
        'purchasable_type',
        'quantity',
        'checkout_reference',
        'checked_out_at',
    ];

    protected $casts = [
        'meta' => 'array',
        'checkout_expiration' => 'datetime',
        'checked_out_at' => 'datetime',
    ];

    public function cart()
    {
        return $this->belongsTo(Cart::class);
    }

    public function purchasable()
    {
        return $this->morphTo();
    }

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }

    public function registerMediaCollections(): void
    {
        $registerMediaConversions = function (Media $media) {
            $this->addMediaConversion('preview');
        };

        $this->addMediaCollection('cart_line_notes')
            ->singleFile()
            ->registerMediaConversions($registerMediaConversions);
    }
}
