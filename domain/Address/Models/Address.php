<?php

declare(strict_types=1);

namespace Domain\Address\Models;

use Domain\Address\Enums\AddressLabelAs;
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
 * @property int $customer_id
 * @property int $state_id
 * @property AddressLabelAs $label_as
 * @property string $address_line_1
 * @property string $zip_code
 * @property string $city
 * @property bool $is_default_shipping
 * @property bool $is_default_billing
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Activitylog\Models\Activity> $activities
 * @property-read int|null $activities_count
 * @property-read Customer|null $customer
 * @property-read \Domain\Address\Models\State $state
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Address newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Address newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Address query()
 * @method static \Illuminate\Database\Eloquent\Builder|Address whereAddressLine1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Address whereCity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Address whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Address whereCustomerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Address whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Address whereIsDefaultBilling($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Address whereIsDefaultShipping($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Address whereLabelAs($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Address whereStateId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Address whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Address whereZipCode($value)
 *
 * @mixin \Eloquent
 */
class Address extends Model
{
    use LogsActivity;

    protected $fillable = [
        'customer_id',
        'state_id',
        'label_as',
        'address_line_1',
        'zip_code',
        'city',
        'is_default_shipping',
        'is_default_billing',
    ];

    protected function casts(): array
    {
        return [
            'label_as' => AddressLabelAs::class,
            'is_default_billing' => 'bool',
            'is_default_shipping' => 'bool',
        ];
    }

    /** @return Attribute<string, never> */
    protected function fullDetail(): Attribute
    {
        return Attribute::get(
            fn ($value): string => Arr::join(
                array_filter([
                    $this->address_line_1,
                    $this->state->country->name,
                    $this->state->name,
                    $this->zip_code,
                    $this->city,
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

    /** @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\Domain\Customer\Models\Customer, $this> */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /** @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\Domain\Address\Models\State, $this> */
    public function state(): BelongsTo
    {
        return $this->belongsTo(State::class);
    }
}
