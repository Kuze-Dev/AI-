<?php

declare(strict_types=1);

namespace Domain\Address\Models;

use Domain\Customer\Models\Customer;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Arr;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * Domain\Customer\Models\Tier
 *
 * @property int $id
 * @property-read  string $full_detail
 * @property int $customer_id
 * @property int $country_id
 * @property int|null $state_id
 * @property int|null $region_id
 * @property int $city_id
 * @property string $address_line_1
 * @property string|null $address_line_2
 * @property string $zip_code
 * @property bool $is_default_shipping
 * @property bool $is_default_billing
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Activitylog\Models\Activity> $activities
 * @property-read int|null $activities_count
 * @property-read \Domain\Address\Models\City|null $city
 * @property-read \Domain\Address\Models\Country|null $country
 * @property-read Customer|null $customer
 * @property-read \Domain\Address\Models\Region|null $region
 * @property-read \Domain\Address\Models\State|null $state
 * @method static \Illuminate\Database\Eloquent\Builder|Address newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Address newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Address query()
 * @method static \Illuminate\Database\Eloquent\Builder|Address whereAddressLine1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Address whereAddressLine2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Address whereCityId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Address whereCountryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Address whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Address whereCustomerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Address whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Address whereIsDefaultBilling($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Address whereIsDefaultShipping($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Address whereRegionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Address whereStateId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Address whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Address whereZipCode($value)
 * @mixin \Eloquent
 */
class Address extends Model
{
    use LogsActivity;

    protected $fillable = [
        'customer_id',
        'address_line_1',
        'address_line_2',
        'country_id',
        'state_id',
        'region_id',
        'city_id',
        'zip_code',
        'is_default_shipping',
        'is_default_billing',
    ];

    protected $casts = [
        'is_default_billing' => 'bool',
        'is_default_shipping' => 'bool',
    ];

    /** @return Attribute<string, never> */
    protected function fullDetail(): Attribute
    {
        return Attribute::get(
            fn ($value) => Arr::join(
                array_filter([
                    $this->address_line_1,
                    $this->address_line_2,
                    $this->country,
                    $this->state_or_region,
                    $this->city_or_province,
                    $this->zip_code,
                ]),
                ', '
            )
        );
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    /** @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\Domain\Customer\Models\Customer, \Domain\Address\Models\Address> */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /** @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\Domain\Address\Models\Country, \Domain\Address\Models\Address> */
    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    /** @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\Domain\Address\Models\State, \Domain\Address\Models\Address> */
    public function state(): BelongsTo
    {
        return $this->belongsTo(State::class);
    }

    /** @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\Domain\Address\Models\Region, \Domain\Address\Models\Address> */
    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    /** @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\Domain\Address\Models\City, \Domain\Address\Models\Address> */
    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }
}
