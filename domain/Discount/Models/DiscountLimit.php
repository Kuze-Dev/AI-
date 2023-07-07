<?php

declare(strict_types=1);

namespace Domain\Discount\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Domain\Discount\Models\DiscountLimit
 *
 * @property int $id
 * @property int|null $discount_id
 * @property int $customer_id
 * @property string $code
 * @property int|null $times_used
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|DiscountLimit newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DiscountLimit newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DiscountLimit query()
 * @method static \Illuminate\Database\Eloquent\Builder|DiscountLimit whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DiscountLimit whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DiscountLimit whereCustomerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DiscountLimit whereDiscountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DiscountLimit whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DiscountLimit whereTimesUsed($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DiscountLimit whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class DiscountLimit extends Model
{
    protected $fillable = [
        'discount_id',
        'user_type',
        'user_id',
        'order_type',
        'order_id',
        'code',
    ];
}
