<?php

declare(strict_types=1);

namespace Domain\Order\Models;

use Domain\Address\Enums\AddressLabelAs;
use Domain\Order\Enums\OrderAddressTypes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * Domain\Order\Models\OrderAddress
 *
 * @property int $id
 * @property int $order_id
 * @property OrderAddressTypes $type
 * @property string $country
 * @property string $state
 * @property AddressLabelAs $label_as
 * @property string $address_line_1
 * @property string $zip_code
 * @property string $city
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Domain\Order\Models\Order|null $order
 *
 * @method static \Illuminate\Database\Eloquent\Builder|OrderAddress newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|OrderAddress newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|OrderAddress query()
 * @method static \Illuminate\Database\Eloquent\Builder|OrderAddress whereAddressLine1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderAddress whereCity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderAddress whereCountry($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderAddress whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderAddress whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderAddress whereLabelAs($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderAddress whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderAddress whereState($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderAddress whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderAddress whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderAddress whereZipCode($value)
 *
 * @mixin \Eloquent
 */
class OrderAddress extends Model
{
    use LogsActivity;

    protected $fillable = [
        'order_id',
        'type',
        'country',
        'state',
        'label_as',
        'address_line_1',
        'zip_code',
        'city',
    ];

    protected function casts(): array
    {
        return [
            'label_as' => AddressLabelAs::class,
            'type' => OrderAddressTypes::class,
        ];
    }

    /** @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\Domain\Order\Models\Order, $this> */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
