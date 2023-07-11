<?php

declare(strict_types=1);

namespace Domain\Cart\Models;

use Domain\Customer\Models\Customer;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Support\ConstraintsRelationships\Attributes\OnDeleteCascade;
use Support\ConstraintsRelationships\ConstraintsRelationships;

/**
 * Domain\Cart\Models\Cart
 *
 * @property int $id
 * @property int $customer_id
 * @property string|null $coupon_code
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Domain\Cart\Models\CartLine> $cartLines
 * @property-read int|null $cart_lines_count
 * @property-read Customer|null $customer
 * @method static \Illuminate\Database\Eloquent\Builder|Cart newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Cart newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Cart query()
 * @method static \Illuminate\Database\Eloquent\Builder|Cart whereCouponCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cart whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cart whereCustomerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cart whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cart whereUpdatedAt($value)
 * @mixin \Eloquent
 */

#[OnDeleteCascade(['cartLines'])]
class Cart extends Model
{
    use HasFactory;
    use ConstraintsRelationships;

    protected $fillable = [
        'customer_id',
        'coupon_code',
    ];

    /** @return \Illuminate\Database\Eloquent\Relations\HasMany<\Domain\Cart\Models\CartLine> */
    public function cartLines()
    {
        return $this->hasMany(CartLine::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
