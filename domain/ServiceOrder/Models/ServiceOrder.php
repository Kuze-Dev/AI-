<?php

declare(strict_types=1);

namespace Domain\ServiceOrder\Models;

use Domain\ServiceOrder\Enums\ServiceOrderStatus;
use Illuminate\Database\Eloquent\Model;

/**
 * @property mixed|null $blueprint
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
        'customer_mobile_no',
        'customer_form',
        'service_address',
        'billing_address',
        'currency_code',
        'currency_name',
        'currency_symbol',
        'service_name',
        'service_price',
        'schedule',
        'status',
        'cancelled_reason',
        'total_price',
    ];

    protected $casts = [
        'customer_form' => 'json',
        'schedule' => 'dateTime',
        'status' => ServiceOrderStatus::class,
    ];

    public function getIsCreatedByAdminAttribute(): bool
    {
        return $this->created_by !== null;
    }
}
