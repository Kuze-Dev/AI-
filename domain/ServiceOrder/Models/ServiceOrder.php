<?php

declare(strict_types=1);

namespace Domain\ServiceOrder\Models;

use Akaunting\Money\Money;
use App\Casts\MoneyCast;
use Domain\Admin\Models\Admin;
use Domain\Customer\Models\Customer;
use Domain\PaymentMethod\Models\PaymentMethod;
use Domain\Payments\Interfaces\PayableInterface;
use Domain\Payments\Models\Payment;
use Domain\Payments\Models\Traits\HasPayments;
use Domain\Service\Enums\BillingCycleEnum;
use Domain\Service\Models\Service;
use Domain\ServiceOrder\Enums\PaymentPlanType;
use Domain\ServiceOrder\Enums\PaymentPlanValue;
use Domain\ServiceOrder\Enums\ServiceBillStatus;
use Domain\ServiceOrder\Enums\ServiceOrderAddressType;
use Domain\ServiceOrder\Enums\ServiceOrderStatus;
use Domain\ServiceOrder\Enums\ServiceTransactionStatus;
use Domain\ServiceOrder\Queries\ServiceOrderQueryBuilder;
use Domain\Taxation\Enums\PriceDisplay;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

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
 * @property string|null $customer_mobile
 * @property array $customer_form
 * @property array $additional_charges
 * @property string $currency_code
 * @property string $currency_name
 * @property string $currency_symbol
 * @property string $service_name
 * @property int $service_price
 * @property BillingCycleEnum|null $billing_cycle
 * @property int|null $due_date_every
 * @property bool $pay_upfront
 * @property bool $is_subscription
 * @property bool $needs_approval
 * @property bool $is_auto_generated_bill
 * @property bool $is_partial_payment
 * @property \Illuminate\Support\Carbon $schedule
 * @property ServiceOrderStatus $status
 * @property string|null $cancelled_reason
 * @property float $sub_total
 * @property PriceDisplay $tax_display
 * @property float $tax_percentage
 * @property float $tax_total
 * @property float $total_price
 * @property PaymentPlanType $payment_type
 * @property PaymentPlanValue $payment_value
 * @property array|null $payment_plan
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Activitylog\Models\Activity> $activities
 * @property-read int|null $activities_count
 * @property-read Admin|null $admin
 * @property-read Customer|null $customer
 * @property-read Service $service
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Domain\ServiceOrder\Models\ServiceBill> $serviceBills
 * @property-read int|null $service_bills_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Domain\ServiceOrder\Models\ServiceOrderAddress> $serviceOrderAddress
 * @property-read int|null $service_order_address_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Domain\ServiceOrder\Models\ServiceTransaction> $serviceTransactions
 * @property-read int|null $service_transactions_count
 * @property-read string $customer_full_name
 * @property-read string $format_status_for_display
 * @property-read string $badge_color_for_status_display
 * @property-read string $format_service_price_for_display
 * @property-read string $format_tax_percentage_for_display
 * @property-read string $format_tax_for_display
 *
 * @method static ServiceOrderQueryBuilder|ServiceOrder newModelQuery()
 * @method static ServiceOrderQueryBuilder|ServiceOrder newQuery()
 * @method static ServiceOrderQueryBuilder|ServiceOrder query()
 * @method static ServiceOrderQueryBuilder|ServiceOrder whereActive()
 * @method static ServiceOrderQueryBuilder|ServiceOrder whereInactive()
 * @method static ServiceOrderQueryBuilder|ServiceOrder whereActiveOrInactive()
 * @method static ServiceOrderQueryBuilder|ServiceOrder whereAdditionalCharges($value)
 * @method static ServiceOrderQueryBuilder|ServiceOrder whereAdminId($value)
 * @method static ServiceOrderQueryBuilder|ServiceOrder whereAutoGenerateBills()
 * @method static ServiceOrderQueryBuilder|ServiceOrder whereBillingCycle($value)
 * @method static ServiceOrderQueryBuilder|ServiceOrder whereCancelledReason($value)
 * @method static ServiceOrderQueryBuilder|ServiceOrder whereCreatedAt($value)
 * @method static ServiceOrderQueryBuilder|ServiceOrder whereCurrencyCode($value)
 * @method static ServiceOrderQueryBuilder|ServiceOrder whereCurrencyName($value)
 * @method static ServiceOrderQueryBuilder|ServiceOrder whereCurrencySymbol($value)
 * @method static ServiceOrderQueryBuilder|ServiceOrder whereCustomerEmail($value)
 * @method static ServiceOrderQueryBuilder|ServiceOrder whereCustomerFirstName($value)
 * @method static ServiceOrderQueryBuilder|ServiceOrder whereCustomerForm($value)
 * @method static ServiceOrderQueryBuilder|ServiceOrder whereCustomerId($value)
 * @method static ServiceOrderQueryBuilder|ServiceOrder whereCustomerLastName($value)
 * @method static ServiceOrderQueryBuilder|ServiceOrder whereCustomerMobile($value)
 * @method static ServiceOrderQueryBuilder|ServiceOrder whereDueDateEvery($value)
 * @method static ServiceOrderQueryBuilder|ServiceOrder whereId($value)
 * @method static ServiceOrderQueryBuilder|ServiceOrder whereIsAutoGeneratedBill($value)
 * @method static ServiceOrderQueryBuilder|ServiceOrder whereIsSubscription($value)
 * @method static ServiceOrderQueryBuilder|ServiceOrder whereNeedsApproval($value)
 * @method static ServiceOrderQueryBuilder|ServiceOrder wherePayUpfront($value)
 * @method static ServiceOrderQueryBuilder|ServiceOrder whereReference($value)
 * @method static ServiceOrderQueryBuilder|ServiceOrder whereSchedule($value)
 * @method static ServiceOrderQueryBuilder|ServiceOrder whereServiceId($value)
 * @method static ServiceOrderQueryBuilder|ServiceOrder whereServiceName($value)
 * @method static ServiceOrderQueryBuilder|ServiceOrder whereServicePrice($value)
 * @method static ServiceOrderQueryBuilder|ServiceOrder whereShouldAutoGenerateBill()
 * @method static ServiceOrderQueryBuilder|ServiceOrder whereStatus($value)
 * @method static ServiceOrderQueryBuilder|ServiceOrder whereSubTotal($value)
 * @method static ServiceOrderQueryBuilder|ServiceOrder whereSubscriptionBased()
 * @method static ServiceOrderQueryBuilder|ServiceOrder whereTaxDisplay($value)
 * @method static ServiceOrderQueryBuilder|ServiceOrder whereTaxPercentage($value)
 * @method static ServiceOrderQueryBuilder|ServiceOrder whereTaxTotal($value)
 * @method static ServiceOrderQueryBuilder|ServiceOrder whereTotalPrice($value)
 * @method static ServiceOrderQueryBuilder|ServiceOrder whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class ServiceOrder extends Model implements PayableInterface
{
    use HasPayments;
    use LogsActivity;
    use SoftDeletes;

    protected $fillable = [
        'service_id',
        'customer_id',
        'admin_id',
        'reference',
        'customer_first_name',
        'customer_last_name',
        'customer_email',
        'customer_mobile',
        'customer_form',
        'additional_charges',
        'currency_code',
        'currency_name',
        'currency_symbol',
        'service_name',
        'service_price',
        'billing_cycle',
        'due_date_every',
        'pay_upfront',
        'is_subscription',
        'needs_approval',
        'is_auto_generated_bill',
        'is_partial_payment',
        'schedule',
        'status',
        'cancelled_reason',
        'sub_total',
        'tax_display',
        'tax_percentage',
        'tax_total',
        'total_price',
        'retail_price',
        'schema',
        'payment_value',
        'payment_plan',
        'payment_type',
    ];

    protected function casts(): array
    {
        return [
            'schema' => 'json',
            'customer_form' => 'json',
            'service_price' => MoneyCast::class,
            'additional_charges' => 'array',
            'billing_cycle' => BillingCycleEnum::class,
            'pay_upfront' => 'boolean',
            'is_subscription' => 'boolean',
            'needs_approval' => 'boolean',
            'is_auto_generated_bill' => 'boolean',
            'is_partial_payment' => 'boolean',
            'schedule' => 'datetime',
            'sub_total' => MoneyCast::class,
            'tax_display' => PriceDisplay::class,
            'tax_percentage' => 'float',
            'tax_total' => MoneyCast::class,
            'total_price' => MoneyCast::class,
            'status' => ServiceOrderStatus::class,
            'payment_plan' => 'json',
            'payment_type' => PaymentPlanType::class,
            'payment_value' => PaymentPlanValue::class,
        ];
    }

    #[\Override]
    public function getRouteKeyName(): string
    {
        return 'reference';
    }

    #[\Override]
    public function getReferenceNumber(): string
    {
        return $this->reference;
    }

    #[\Override]
    public function newEloquentBuilder($query): ServiceOrderQueryBuilder
    {
        return new ServiceOrderQueryBuilder($query);
    }

    /** @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\Domain\Customer\Models\Customer, $this> */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /** @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\Domain\Service\Models\Service, $this> */
    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    /** @return \Illuminate\Database\Eloquent\Relations\HasMany<\Domain\ServiceOrder\Models\ServiceTransaction, $this>*/
    public function serviceTransactions(): HasMany
    {
        return $this->hasMany(ServiceTransaction::class);
    }

    /** @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\Domain\Admin\Models\Admin, $this> */
    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class);
    }

    /** @return \Illuminate\Database\Eloquent\Relations\HasMany<\Domain\ServiceOrder\Models\ServiceOrderAddress, $this>*/
    public function serviceOrderAddress(): HasMany
    {
        return $this->hasMany(ServiceOrderAddress::class);
    }

    /** @return \Illuminate\Database\Eloquent\Relations\HasOne<\Domain\ServiceOrder\Models\ServiceOrderAddress, $this>*/
    public function serviceOrderServiceAddress(): HasOne
    {
        return $this->hasOne(ServiceOrderAddress::class)
            ->whereType(ServiceOrderAddressType::SERVICE_ADDRESS);
    }

    /** @return \Illuminate\Database\Eloquent\Relations\HasOne<\Domain\ServiceOrder\Models\ServiceOrderAddress, $this>*/
    public function serviceOrderBillingAddress(): HasOne
    {
        return $this->hasOne(ServiceOrderAddress::class)
            ->whereType(ServiceOrderAddressType::BILLING_ADDRESS);
    }

    /** @return \Illuminate\Database\Eloquent\Relations\HasMany<\Domain\ServiceOrder\Models\ServiceBill, $this>*/
    public function serviceBills(): HasMany
    {
        return $this->hasMany(ServiceBill::class);
    }

    public function latestServiceBill(): ?ServiceBill
    {
        return $this->serviceBills()
            ->latest()
            ->first();
    }

    public function totalBalance(): Money
    {
        return money(
            $this->serviceBills()
                ->where('status', 'pending')
                ->sum('total_balance'),
            $this->currency_code,
        );
    }

    public function totalUnpaidBills(): int
    {
        return $this->serviceBills()->where('total_balance', '>', 0)->count();
    }

    public function totalBalanceTax(): Money
    {
        return money($this->serviceBills()
            ->where('status', 'pending')
            ->sum('tax_total'));
    }

    public function totalBalanceSubtotal(): Money
    {
        return money($this->serviceBills()
            ->where('status', 'pending')
            ->sum('sub_total'));
    }

    public function latestPaidServiceBill(): ?ServiceBill
    {
        /** @var \Domain\ServiceOrder\Models\ServiceBill $serviceBill */
        $serviceBill = $this->latestServiceBill();

        return filled($serviceBill) && $serviceBill->status === ServiceBillStatus::PAID
            ? $serviceBill
            : null;
    }

    public function latestTransaction(): ?ServiceTransaction
    {
        return $this->serviceTransactions()
            ->latest()
            ->first();
    }

    public function latestPaidTransaction(): ?ServiceTransaction
    {
        /** @var \Domain\ServiceOrder\Models\ServiceTransaction $serviceTransaction */
        $serviceTransaction = $this->latestTransaction();

        return filled($serviceTransaction) && $serviceTransaction->status === ServiceTransactionStatus::PAID
            ? $serviceTransaction
            : null;
    }

    public function latestPaymentMethod(): ?PaymentMethod
    {
        /** @var \Domain\ServiceOrder\Models\ServiceTransaction $serviceTransaction. */
        $serviceTransaction = $this->latestPaidTransaction();

        return filled($serviceTransaction) ?
        $serviceTransaction->payment_method : null;
    }

    public function latestPayment(): ?Payment
    {
        /** @var \Domain\ServiceOrder\Models\ServiceTransaction $serviceTransaction. */
        $serviceTransaction = $this->latestTransaction();

        return filled($serviceTransaction) ?
        $serviceTransaction->payment : null;
    }

    public function latestPendingServiceBill(): ?ServiceBill
    {
        /** @var \Domain\ServiceOrder\Models\ServiceBill $serviceBill */
        $serviceBill = $this->latestServiceBill();

        return filled($serviceBill) && $serviceBill->status === ServiceBillStatus::PENDING
            ? $serviceBill
            : null;
    }

    public function serviceBillingAddress(): ?ServiceOrderAddress
    {
        return $this->serviceOrderAddress
            ->where('type', ServiceOrderAddressType::BILLING_ADDRESS)
            ->first();
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    /** @return Attribute<string, never> */
    protected function customerFullName(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value) => "{$this->customer_first_name} {$this->customer_last_name}",
        );
    }

    /** @return Attribute<string, never> */
    protected function formatStatusForDisplay(): Attribute
    {
        return Attribute::make(
            get: function (mixed $value) {
                $value = Str::replace('_', ' ', $this->status->value);
                $value = Str::ucfirst($value);

                return $value;
            },
        );
    }

    /** @return Attribute<string, never> */
    protected function badgeColorForStatusDisplay(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value) => match ($this->status->value) {
                'pending', 'in_progress' => 'warning',
                'closed', 'inactive' => 'danger',
                'completed', 'active' => 'success',
                'for_payment' => 'secondary',
                'for_approval' => 'secondary',
            },
        );
    }

    /** @return Attribute<\Akaunting\Money\Money, never> */
    protected function formatServicePriceForDisplay(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value) => money($this->service_price * 100, $this->currency_code),
        );
    }

    /** @return Attribute<string, never> */
    protected function formatTaxPercentageForDisplay(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value) => "Tax ({$this->tax_percentage}%)",
        );
    }

    /** @return Attribute<string, never> */
    protected function formatTaxForDisplay(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value) => Str::ucfirst($this->tax_display->value),
        );
    }
}
