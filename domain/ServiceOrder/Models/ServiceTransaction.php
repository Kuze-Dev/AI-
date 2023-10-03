<?php

declare(strict_types=1);

namespace Domain\ServiceOrder\Models;

use Domain\PaymentMethod\Models\PaymentMethod;
use Domain\Payments\Models\Payment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceTransaction extends Model
{
    protected $fillable = [
        'service_order_id',
        'service_bill_id',
        'payment_id',
        'payment_method_id',
        'currency',
        'total_amount',
        'status',
    ];

    protected $casts = [
        'total_amount' => 'float',
    ];

    /** @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\Domain\ServiceOrder\Models\ServiceOrder, \Domain\ServiceOrder\Models\ServiceTransaction> */
    public function service_order(): BelongsTo
    {
        return $this->belongsTo(ServiceOrder::class);
    }

    /** @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\Domain\ServiceOrder\Models\ServiceBill, \Domain\ServiceOrder\Models\ServiceTransaction> */
    public function service_bill(): BelongsTo
    {
        return $this->belongsTo(ServiceBill::class);
    }

    /** @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\Domain\Payments\Models\Payment, \Domain\ServiceOrder\Models\ServiceTransaction> */
    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    /** @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\Domain\PaymentMethod\Models\PaymentMethod, \Domain\ServiceOrder\Models\ServiceTransaction> */
    public function payment_method(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class);
    }
}
