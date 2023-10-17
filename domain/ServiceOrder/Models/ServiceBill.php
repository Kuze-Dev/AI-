<?php

declare(strict_types=1);

namespace Domain\ServiceOrder\Models;

use Domain\Payments\Interfaces\PayableInterface;
use Domain\Payments\Models\Traits\HasPayments;
use Domain\ServiceOrder\Enums\ServiceBillStatus;
use Domain\ServiceOrder\Queries\ServiceBillQueryBuilder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Domain\ServiceOrder\Models\ServiceBill
 *
 * @property int $id
 * @property int $service_order_id
 * @property string $reference
 * @property \Illuminate\Support\Carbon|null $bill_date
 * @property \Illuminate\Support\Carbon|null $due_date
 * @property float $service_price
 * @property array $additional_charges
 * @property float $total_amount
 * @property ServiceBillStatus $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Domain\Payments\Models\Payment> $payments
 * @property-read int|null $payments_count
 * @property-read \Domain\ServiceOrder\Models\ServiceOrder $serviceOrder
 * @method static \Illuminate\Database\Eloquent\Builder|ServiceBill newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ServiceBill newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ServiceBill query()
 * @method static \Illuminate\Database\Eloquent\Builder|ServiceBill whereAdditionalCharges($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ServiceBill whereBillDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ServiceBill whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ServiceBill whereDueDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ServiceBill whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ServiceBill whereReference($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ServiceBill whereServiceOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ServiceBill whereServicePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ServiceBill whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ServiceBill whereTotalAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ServiceBill whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class ServiceBill extends Model implements PayableInterface
{
    use HasPayments;

    protected $fillable = [
        'service_order_id',
        'bill_date',
        'due_date',
        'reference',
        'service_price',
        'additional_charges',
        'total_amount',
        'status',
        'email_notification_sent_at',
    ];

    protected $casts = [
        'bill_date' => 'datetime',
        'due_date' => 'datetime',
        'additional_charges' => 'array',
        'service_price' => 'float',
        'total_amount' => 'float',
        'status' => ServiceBillStatus::class,
        'email_notification_sent_at' => 'datetime',
    ];

    public function getRouteKeyName(): string
    {
        return 'reference';
    }

    public function getReferenceNumber(): string
    {
        return $this->reference;
    }

    public function newEloquentBuilder($query): ServiceBillQueryBuilder
    {
        return new ServiceBillQueryBuilder($query);
    }

    /** @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\Domain\ServiceOrder\Models\ServiceOrder, \Domain\ServiceOrder\Models\ServiceBill> */
    public function serviceOrder(): BelongsTo
    {
        return $this->belongsTo(ServiceOrder::class);
    }
}
