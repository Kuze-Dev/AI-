<?php

declare(strict_types=1);

namespace Domain\ServiceOrder\Models;

use App\Casts\MoneyCast;
use Domain\PaymentMethod\Models\PaymentMethod;
use Domain\Payments\Models\Payment;
use Domain\ServiceOrder\Enums\ServiceTransactionStatus;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * Domain\ServiceOrder\Models\ServiceTransaction
 *
 * @property int $id
 * @property int $service_order_id
 * @property int|null $service_bill_id
 * @property int $payment_id
 * @property int $payment_method_id
 * @property string $currency
 * @property float $total_amount
 * @property ServiceTransactionStatus $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Payment|null $payment
 * @property-read PaymentMethod|null $payment_method
 * @property-read \Domain\ServiceOrder\Models\ServiceBill|null $serviceBill
 * @property-read \Domain\ServiceOrder\Models\ServiceOrder|null $serviceOrder
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Activitylog\Models\Activity> $activities
 * @property-read bool $is_paid
 *
 * @method static \Illuminate\Database\Eloquent\Builder|ServiceTransaction newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ServiceTransaction newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ServiceTransaction query()
 * @method static \Illuminate\Database\Eloquent\Builder|ServiceTransaction whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ServiceTransaction whereCurrency($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ServiceTransaction whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ServiceTransaction wherePaymentMethodId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ServiceTransaction whereServiceBillId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ServiceTransaction whereServiceOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ServiceTransaction whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ServiceTransaction whereTotalAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ServiceTransaction whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class ServiceTransaction extends Model
{
    use LogsActivity;
    use SoftDeletes;

    protected $fillable = [
        'service_order_id',
        'service_bill_id',
        'payment_id',
        'payment_method_id',
        'currency',
        'total_amount',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'total_amount' => MoneyCast::class,
            'status' => ServiceTransactionStatus::class,
        ];
    }

    public function getTotalAmountWithCurrency(): string
    {
        return $this->currency.
            ''.
            number_format((float) $this->total_amount, 2, '.', ',');
    }

    public function getStatusColor(): string
    {
        return match (
            str_replace(
                ' ',
                '_',
                strtolower($this->status->value)
            )
        ) {
            ServiceTransactionStatus::PAID->value => 'success',
            ServiceTransactionStatus::PENDING->value => 'warning',
            ServiceTransactionStatus::REFUNDED->value => 'danger',
            default => 'secondary',
        };
    }

    /** @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\Domain\ServiceOrder\Models\ServiceOrder, $this> */
    public function serviceOrder(): BelongsTo
    {
        return $this->belongsTo(ServiceOrder::class);
    }

    /** @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\Domain\ServiceOrder\Models\ServiceBill, $this> */
    public function serviceBill(): BelongsTo
    {
        return $this->belongsTo(ServiceBill::class);
    }

    /** @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\Domain\Payments\Models\Payment, $this> */
    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    /** @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\Domain\PaymentMethod\Models\PaymentMethod, $this> */
    public function payment_method(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    /** @return Attribute<bool, never> */
    protected function isPaid(): Attribute
    {
        return Attribute::get(
            fn (mixed $value) => $this->status === ServiceTransactionStatus::PAID
        );
    }
}
