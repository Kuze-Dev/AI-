<?php

declare(strict_types=1);

namespace Domain\ServiceOrder\Models;

use Domain\ServiceOrder\Enums\ServiceOrderStatus;
use Illuminate\Database\Eloquent\Model;
use Domain\Admin\Models\Admin;
use Domain\Customer\Models\Customer;
use Domain\Service\Enums\BillingCycle;
use Domain\Service\Models\Service;
use Domain\ServiceOrder\Enums\ServiceBillStatus;
use Domain\ServiceOrder\Queries\ServiceOrderQueryBuilder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Domain\ServiceOrder\Models\ServiceOrder
 *
 * @property int $id
 * @property int $service_id
 * @property int $customer_id
 * @property int|null $admin_id
 * @property string $reference
 * @property string $customer_first_name
 * @property string $customer_last_name
 * @property string $customer_email
 * @property string $customer_mobile
 * @property array $customer_form
 * @property array $additional_charges
 * @property string $currency_code
 * @property string $currency_name
 * @property string $currency_symbol
 * @property string $service_name
 * @property float $service_price
 * @property BillingCycle $billing_cycle
 * @property int $due_date_every
 * @property \Illuminate\Support\Carbon $schedule
 * @property ServiceOrderStatus $status
 * @property string|null $cancelled_reason
 * @property float $total_price
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Admin|null $admin
 * @property-read Customer|null $customer
 * @property-read Service|null $service
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Domain\ServiceOrder\Models\ServiceOrderAddress> $serviceOrderAddress
 * @property-read int|null $service_order_address_count
 * @method static \Illuminate\Database\Eloquent\Builder|ServiceOrder newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ServiceOrder newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ServiceOrder query()
 * @method static \Illuminate\Database\Eloquent\Builder|ServiceOrder whereAdditionalCharges($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ServiceOrder whereAdminId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ServiceOrder whereBillingCycle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ServiceOrder whereCancelledReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ServiceOrder whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ServiceOrder whereCurrencyCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ServiceOrder whereCurrencyName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ServiceOrder whereCurrencySymbol($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ServiceOrder whereCustomerEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ServiceOrder whereCustomerFirstName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ServiceOrder whereCustomerForm($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ServiceOrder whereCustomerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ServiceOrder whereCustomerLastName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ServiceOrder whereCustomerMobile($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ServiceOrder whereDueDateEvery($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ServiceOrder whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ServiceOrder whereReference($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ServiceOrder whereSchedule($value)
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
        'created_by',
        'service_id',
        'customer_id',
        'admin_id',
        'customer_first_name',
        'customer_last_name',
        'customer_email',
        'customer_mobile',
        'customer_form',
        'reference',
        'service_address',
        'additional_charges',
        'currency_code',
        'billing_cycle',
        'due_date_every',
        'currency_name',
        'currency_symbol',
        'service_name',
        'service_price',
        'billing_cycle',
        'due_date_every',
        'schedule',
        'status',
        'cancelled_reason',
        'total_price',
        'service_address_id',
        'billing_address_id',
        'is_same_as_billing',
    ];

    protected $casts = [
        'customer_form' => 'json',
        'service_price' => 'float',
        'additional_charges' => 'json',
        'billing_cycle' => BillingCycle::class,
        'schedule' => 'datetime',
        'total_price' => 'float',
        'status' => ServiceOrderStatus::class,
    ];

    public function getRouteKeyName(): string
    {
        return 'reference';
    }

    public function newEloquentBuilder($query): ServiceOrderQueryBuilder
    {
        return new ServiceOrderQueryBuilder($query);
    }

    /** @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\Domain\Customer\Models\Customer, \Domain\ServiceOrder\Models\ServiceOrder> */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /** @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\Domain\Service\Models\Service, \Domain\ServiceOrder\Models\ServiceOrder> */
    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    /** @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\Domain\Admin\Models\Admin, \Domain\ServiceOrder\Models\ServiceOrder> */
    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class);
    }

    /** @return \Illuminate\Database\Eloquent\Relations\HasMany<\Domain\ServiceOrder\Models\ServiceOrderAddress>*/
    public function serviceOrderAddress(): HasMany
    {
        return $this->hasMany(ServiceOrderAddress::class);
    }

    /** @return \Illuminate\Database\Eloquent\Relations\HasMany<\Domain\ServiceOrder\Models\ServiceBill>*/
    public function serviceBills(): HasMany
    {
        return $this->hasMany(ServiceBill::class);
    }

    public function latestPaidServiceBill(): ?ServiceBill
    {
        /** @var \Domain\ServiceOrder\Models\ServiceBill $serviceBill */
        $serviceBill = $this->serviceBills()
            ->latest()
            ->first();

        return filled($serviceBill) && $serviceBill->status === ServiceBillStatus::PAID
            ? $serviceBill
            : null;
    }
}
