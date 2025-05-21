<?php

declare(strict_types=1);

namespace Domain\Shipment\Models;

use Domain\ShippingMethod\Models\ShippingMethod;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Support\ConstraintsRelationships\ConstraintsRelationships;

/**
 * Domain\Shipment\Models\Shipment
 *
 * @property int $id
 * @property string $model_type
 * @property int $model_id
 * @property int $shipping_method_id
 * @property string|null $tracking_id
 * @property string|null $status
 * @property string $rate
 * @property array|null $shipping_details
 * @property array|null $destination_address
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Activitylog\Models\Activity> $activities
 * @property-read int|null $activities_count
 * @property-read ShippingMethod|null $shippingMethod
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Shipment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Shipment newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Shipment query()
 * @method static \Illuminate\Database\Eloquent\Builder|Shipment whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Shipment whereDestinationAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Shipment whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Shipment whereModelId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Shipment whereModelType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Shipment whereRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Shipment whereShippingDetails($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Shipment whereShippingMethodId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Shipment whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Shipment whereTrackingId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Shipment whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class Shipment extends Model
{
    use ConstraintsRelationships;
    use LogsActivity;

    protected $fillable = [
        'model_type',
        'model_id',
        'shipping_method_id',
        'tracking_id',
        'shipping_details',
        'destination_address',
        'rate',
    ];

    protected function casts(): array
    {
        return [
            'shipping_details' => 'array',
            'destination_address' => 'array',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    /** @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\Domain\ShippingMethod\Models\ShippingMethod, $this> */
    public function shippingMethod(): BelongsTo
    {
        return $this->belongsTo(ShippingMethod::class);
    }

    #[\Override]
    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
