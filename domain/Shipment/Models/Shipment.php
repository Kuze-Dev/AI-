<?php

declare(strict_types=1);

namespace Domain\Shipment\Models;

use Domain\ShippingMethod\Models\ShippingMethod;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Support\ConstraintsRelationships\ConstraintsRelationships;

class Shipment extends Model
{
    use LogsActivity;
    use ConstraintsRelationships;

    /**
     * Declare columns
     * that are mass assignable.
     */
    protected $fillable = [
        'model_type',
        'model_id',
        'shipping_method_id',
        'tracking_id',
        'shipping_details',
        'destination_address',
        'rate',

    ];

    protected $casts = [
        'shipping_details' => 'array',
        'destination_address' => 'array',
    ];

    /** @return LogOptions */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    /**
     * Declare relationship of
     * current model to shippingMethod.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<ShippingMethod, self>
     */
    public function shippingMethod()
    {
        return $this->belongsTo(ShippingMethod::class);
    }

    /**
     * Set the column reference
     * for route keys.
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
