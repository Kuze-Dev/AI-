<?php

declare(strict_types=1);

namespace Domain\ServiceOrder\Models;

use Domain\Address\Enums\AddressLabelAs;
use Domain\ServiceOrder\Enums\ServiceOrderAddressType;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;

/**
 * Domain\ServiceOrder\Models\ServiceOrderAddress
 *
 * @property int $id
 * @property int $service_order_id
 * @property ServiceOrderAddressType $type
 * @property string $country
 * @property string $state
 * @property AddressLabelAs $label_as
 * @property string $address_line_1
 * @property string $zip_code
 * @property string $city
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Domain\ServiceOrder\Models\ServiceOrder|null $serviceOrder
 *
 * @method static \Illuminate\Database\Eloquent\Builder|ServiceOrderAddress newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ServiceOrderAddress newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ServiceOrderAddress query()
 * @method static \Illuminate\Database\Eloquent\Builder|ServiceOrderAddress whereAddressLine1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ServiceOrderAddress whereCity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ServiceOrderAddress whereCountry($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ServiceOrderAddress whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ServiceOrderAddress whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ServiceOrderAddress whereLabelAs($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ServiceOrderAddress whereServiceOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ServiceOrderAddress whereState($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ServiceOrderAddress whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ServiceOrderAddress whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ServiceOrderAddress whereZipCode($value)
 *
 * @mixin \Eloquent
 */
class ServiceOrderAddress extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'service_order_id',
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
            'type' => ServiceOrderAddressType::class,
        ];
    }

    /** @return Attribute<non-falsy-string, never> */
    protected function fullAddress(): Attribute
    {
        return Attribute::get(
            fn ($value): string => implode(
                ' ',
                array_filter(
                    [
                        $this->label_as->value.': ',
                        $this->address_line_1,
                        $this->city,
                        $this->state,
                        $this->country.', ',
                        $this->zip_code,
                    ]
                )
            )
        );
    }

    /** @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\Domain\ServiceOrder\Models\ServiceOrder, $this> */
    public function serviceOrder(): BelongsTo
    {
        return $this->belongsTo(ServiceOrder::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
