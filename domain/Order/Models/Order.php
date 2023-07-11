<?php

declare(strict_types=1);

namespace Domain\Order\Models;

use Domain\Order\Enums\OrderAddressTypes;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Order extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $fillable = [
        'customer_id',
        'customer_first_name',
        'customer_last_name',
        'customer_mobile',
        'customer_email',
        'currency_code',
        'currency_name',
        'currency_exchange_rate',
        'reference',
        'tax_total',
        'sub_total',
        'discount_total',
        'shipping_total',
        'total',
        'notes',
        'shipping_method',
        'shipping_details',
        'payment_method',
        'payment_details',
        'payment_status',
        'payment_message',
        'is_paid',
        'status',
        'cancelled_reason',
        'cancelled_at',
    ];

    protected $casts = [
        'currency_exchange_rate' => 'float',
        'tax_total' => 'float',
        'sub_total' => 'float',
        'discount_total' => 'float',
        'shipping_total' => 'float',
        'total' => 'float',
        'is_paid' => 'boolean',
        'status' => OrderStatuses::class,
        'cancelled_at' => 'datetime',
    ];

    public function order_lines()
    {
        return $this->hasMany(OrderLine::class);
    }

    public function shipping_address()
    {
        return $this->hasOne(OrderAddress::class)->where('type', OrderAddressTypes::SHIPPING);
    }

    public function billing_address()
    {
        return $this->hasOne(OrderAddress::class)->where('type', OrderAddressTypes::BILLING);
    }

    public function registerMediaCollections(): void
    {
        $registerMediaConversions = function (Media $media) {
            $this->addMediaConversion('preview');
        };

        $this->addMediaCollection('bank_proof_image')
            ->onlyKeepLatest(3)
            ->registerMediaConversions($registerMediaConversions);
    }
}
