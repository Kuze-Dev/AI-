<?php

declare(strict_types=1);

namespace Domain\Address\Models;

use Domain\Customer\Models\Customer;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * Domain\Customer\Models\Tier
 *
 * @property int $id
 * @property-read  string $full_detail
 * @property int $customer_id
 * @property string $address_line_1
 * @property string|null $address_line_2
 * @property string $country
 * @property string|null $state_or_region
 * @property string $city_or_province
 * @property string $zip_code
 * @property bool $is_default_shipping
 * @property bool $is_default_billing
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Activitylog\Models\Activity> $activities
 * @property-read int|null $activities_count
 * @property-read \Domain\Customer\Models\Customer|null $customer
 * @property-read \Domain\Customer\Models\Customer|null $tier
 * @method static \Illuminate\Database\Eloquent\Builder|Address newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Address newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Address query()
 * @method static \Illuminate\Database\Eloquent\Builder|Address whereAddressLine1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Address whereAddressLine2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Address whereCityOrProvince($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Address whereCountry($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Address whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Address whereCustomerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Address whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Address whereIsDefaultBilling($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Address whereIsDefaultShipping($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Address whereStateOrRegion($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Address whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Address whereZipCode($value)
 * @mixin \Eloquent
 */
class Address extends Model
{
    use LogsActivity;

    protected $fillable = [
        'address_line_1',
        'address_line_2',
        'country',
        'state_or_region',
        'city_or_province',
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
            fn ($value) => sprintf(
                '%s, %s, %s, %s, %s, %s',
                $this->address_line_1,
                $this->address_line_2,
                $this->country,
                $this->state_or_region,
                $this->city_or_province,
                $this->zip_code
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

    /** @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\Domain\Customer\Models\Customer, \Domain\Customer\Models\Address> */
    public function tier(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}
