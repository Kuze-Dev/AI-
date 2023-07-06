<?php

declare(strict_types=1);

namespace Domain\Cart\Models;

use Domain\Product\Models\ProductVariant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class CartLine extends Model implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia;

    protected $fillable = [
        'cart_id',
        'purchasable_id',
        'variant_id',
        'purchasable_type',
        'quantity',
        'notes',
        'for_check_out',
        'checked_out_at',
    ];

    protected $casts = [
        'for_check_out' => 'boolean',
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
