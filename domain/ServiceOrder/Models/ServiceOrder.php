<?php

declare(strict_types=1);

namespace Domain\ServiceOrder\Models;

use Domain\ServiceOrder\Enums\ServiceOrderStatus;
use Illuminate\Database\Eloquent\Model;
use dateTime;

/**
 * Domain\ServiceOrder\Models\ServiceOrder
 *
 * @property int $id
 * @property int $service_id
 * @property int $customer_id
 * @property int|null $created_by if not null means created by admin.
 * @property string $customer_first_name
 * @property string $customer_last_name
 * @property string $customer_email
 * @property string $customer_mobile_no
 * @property array $customer_form
 * @property mixed $additional_charges
 * @property string $service_address
 * @property string $billing_address
 * @property string $currency_code
 * @property string $currency_name
 * @property string $currency_symbol
 * @property string $service_name
 * @property string $service_price
 * @property dateTime $schedule
 * @property ServiceOrderStatus $status
 * @property string $cancelled_reason
 * @property string $total_price
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read bool $is_created_by_admin
 * @method static \Illuminate\Database\Eloquent\Builder|ServiceOrder newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ServiceOrder newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ServiceOrder query()
 * @method static \Illuminate\Database\Eloquent\Builder|ServiceOrder whereAdditionalCharges($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ServiceOrder whereBillingAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ServiceOrder whereCancelledReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ServiceOrder whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ServiceOrder whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ServiceOrder whereCurrencyCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ServiceOrder whereCurrencyName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ServiceOrder whereCurrencySymbol($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ServiceOrder whereCustomerEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ServiceOrder whereCustomerFirstName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ServiceOrder whereCustomerForm($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ServiceOrder whereCustomerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ServiceOrder whereCustomerLastName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ServiceOrder whereCustomerMobileNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ServiceOrder whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ServiceOrder whereSchedule($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ServiceOrder whereServiceAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ServiceOrder whereServiceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ServiceOrder whereServiceName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ServiceOrder whereServicePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ServiceOrder whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ServiceOrder whereTotalPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ServiceOrder whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class ServiceOrder extends Model
{
    protected $fillable = [
        'service_id',
        'customer_id',
        'admin_id',
        'customer_first_name',
        'customer_last_name',
        'customer_email',
        'customer_mobile',
        'customer_form',
        'service_address',
        'additional_charges',
        'service_address',
        'billing_address',
        'currency_code',
        'currency_name',
        'currency_symbol',
        'service_name',
        'service_price',
        'is_paid',
        'schedule',
        'status',
        'cancelled_reason',
        'total_price',
    ];

    protected $casts = [
        'customer_form' => 'json',
        'additional_charges' => 'json',
        'schedule' => 'datetime',
        'status' => ServiceOrderStatus::class,
    ];

    public function getIsCreatedByAdminAttribute(): bool
    {
        return $this->created_by !== null;
    }
}
