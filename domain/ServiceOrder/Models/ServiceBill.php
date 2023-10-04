<?php

declare(strict_types=1);

namespace Domain\ServiceOrder\Models;

use Domain\PaymentMethod\Models\PaymentMethod;
use Domain\ServiceOrder\Enums\ServiceBillStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Domain\ServiceOrder\Models\ServiceBill
 *
 * @property int $id
 * @property int $service_order_id
 * @property \Illuminate\Support\Carbon|null $bill_date
 * @property \Illuminate\Support\Carbon|null $due_date
 * @property string $service_price
 * @property array $additional_charges
 * @property float $total_amount
 * @property ServiceBillStatus $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read PaymentMethod|null $payment_method
 * @property-read \Domain\ServiceOrder\Models\ServiceOrder|null $service_order
 * @method static \Illuminate\Database\Eloquent\Builder|ServiceBill newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ServiceBill newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ServiceBill query()
 * @method static \Illuminate\Database\Eloquent\Builder|ServiceBill whereAdditionalCharges($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ServiceBill whereBillDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ServiceBill whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ServiceBill whereDueDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ServiceBill whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ServiceBill whereServiceOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ServiceBill whereServicePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ServiceBill whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ServiceBill whereTotalAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ServiceBill whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class ServiceBill extends Model
{
    protected $fillable = [
        'service_order_id',
        'payment_method_id',
        'bill_date',
        'due_date',
        'service_price',
        'additional_charges',
        'total_amount',
        'status',
    ];

    protected $casts = [
        'bill_date' => 'datetime',
        'due_date' => 'datetime',
        'additional_charges' => 'array',
        'service_price' => 'float',
        'total_amount' => 'float',
        'status' => ServiceBillStatus::class,
    ];

    /** @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\Domain\ServiceOrder\Models\ServiceOrder, \Domain\ServiceOrder\Models\ServiceBill> */
    public function service_order(): BelongsTo
    {
        return $this->belongsTo(ServiceOrder::class);
    }

    /** @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\Domain\PaymentMethod\Models\PaymentMethod, \Domain\ServiceOrder\Models\ServiceBill> */
    public function payment_method(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class);
    }
}
