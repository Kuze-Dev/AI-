<?php

declare(strict_types=1);

namespace Domain\RewardPoint\Models;

use Domain\Customer\Models\Customer;
use Domain\Order\Models\Order;
use Eloquent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Domain\RewardPoint\Models\PointEarning
 *
 * @property int $id
 * @property string $customer_type
 * @property int $customer_id
 * @property string $order_type
 * @property int $order_id
 * @property int $earned_points
 * @property \Illuminate\Support\Carbon $date_earned
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Model|Eloquent $customer
 * @property-read Model|Eloquent $order
 *
 * @method static \Illuminate\Database\Eloquent\Builder|PointEarning newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PointEarning newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PointEarning query()
 * @method static \Illuminate\Database\Eloquent\Builder|PointEarning whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PointEarning whereCustomerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PointEarning whereCustomerType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PointEarning whereDateEarned($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PointEarning whereEarnedPoints($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PointEarning whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PointEarning whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PointEarning whereOrderType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PointEarning whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class PointEarning extends Model
{
    protected $fillable = [
        'customer_type',
        'customer_id',
        'order_type',
        'order_id',
        'date_earned',
        'earned_points',
    ];

    protected function casts(): array
    {
        return [
            'earned_points' => 'float',
            'date_earned' => 'datetime',
        ];
    }

    /** @return \Illuminate\Database\Eloquent\Relations\MorphTo<\Domain\Customer\Models\Customer, $this>*/
    public function customer(): MorphTo
    {
        return $this->morphTo(Customer::class);
    }

    /** @return \Illuminate\Database\Eloquent\Relations\MorphTo<\Domain\Order\Models\Order, $this>*/
    public function order(): MorphTo
    {
        return $this->morphTo(Order::class);
    }
}
