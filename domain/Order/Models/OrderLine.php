<?php

declare(strict_types=1);

namespace Domain\Order\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

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
        'sub_total',
        'discount_total',
        'total',
        'remarks_data',
        'purchasable_data',
    ];

    protected $casts = [
        'unit_price' => 'float',
        'quantity' => 'integer',
        'tax_total' => 'float',
        'sub_total' => 'float',
        'discount_total' => 'float',
        'total' => 'float',
        'remarks_data'  => 'array',
        'purchasable_data' => 'array',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
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
            ->onlyKeepLatest(3)
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
