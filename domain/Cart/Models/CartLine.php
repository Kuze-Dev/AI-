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
 * @property-read \Domain\Cart\Models\Cart|null $cart
 * @property-read \Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection<int, Media> $media
 * @property-read int|null $media_count
 * @property-read Model|\Eloquent $purchasable
 * @property-read ProductVariant|null $variant
 * @method static \Illuminate\Database\Eloquent\Builder|CartLine newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CartLine newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CartLine query()
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
        'meta',
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

    public function registerMediaCollections(): void
    {
        $registerMediaConversions = function (Media $media) {
            $this->addMediaConversion('preview');
        };

        $this->addMediaCollection('cart_line_notes')
            ->onlyKeepLatest(3)
            ->registerMediaConversions($registerMediaConversions);
    }
}
