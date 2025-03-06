<?php

declare(strict_types=1);

namespace Domain\Discount\Models;

use Domain\Customer\Models\Customer;
use Domain\Order\Models\Order;
use Eloquent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Domain\Discount\Models\DiscountLimit
 *
 * @property int $id
 * @property int|null $discount_id
 * @property string $customer_type
 * @property int $customer_id
 * @property string $order_type
 * @property int $order_id
 * @property string $code
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Model|Eloquent $customer
 * @property-read \Domain\Discount\Models\Discount|null $discount
 * @property-read Model|Eloquent $order
 *
 * @method static \Illuminate\Database\Eloquent\Builder|DiscountLimit newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DiscountLimit newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DiscountLimit query()
 * @method static \Illuminate\Database\Eloquent\Builder|DiscountLimit whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DiscountLimit whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DiscountLimit whereCustomerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DiscountLimit whereCustomerType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DiscountLimit whereDiscountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DiscountLimit whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DiscountLimit whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DiscountLimit whereOrderType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DiscountLimit whereUpdatedAt($value)
 *
 * @mixin Eloquent
 */
class DiscountLimit extends Model
{
    protected $fillable = [
        'discount_id',
        'customer_id',
        'customer_type',
        'order_type',
        'order_id',
        'code',
    ];

    /** @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\Domain\Discount\Models\Discount, $this> */
    public function discount(): BelongsTo
    {
        return $this->belongsTo(Discount::class);
    }

    /** @return \Illuminate\Database\Eloquent\Relations\MorphTo<\Domain\Customer\Models\Customer, $this> */
    public function customer(): MorphTo
    {
        /** @phpstan-ignore return.type */
        return $this->morphTo(Customer::class);
    }

    /** @return \Illuminate\Database\Eloquent\Relations\MorphTo<\Domain\Order\Models\Order, $this>*/
    public function order(): MorphTo
    {
        /** @phpstan-ignore return.type */
        return $this->morphTo(Order::class);
    }
}
