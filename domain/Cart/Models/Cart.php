<?php

declare(strict_types=1);

namespace Domain\Cart\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Support\ConstraintsRelationships\Attributes\OnDeleteCascade;
use Support\ConstraintsRelationships\ConstraintsRelationships;

#[OnDeleteCascade(['cart_lines'])]
/**
 * Domain\Cart\Models\Cart
 *
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Domain\Cart\Models\CartLine> $cart_lines
 * @property-read int|null $cart_lines_count
 * @method static \Illuminate\Database\Eloquent\Builder|Cart newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Cart newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Cart query()
 * @mixin \Eloquent
 */
class Cart extends Model
{
    use HasFactory;
    use ConstraintsRelationships;

    protected $fillable = [
        'customer_id',
        'coupon_code'
    ];

    public function cart_lines()
    {
        return $this->hasMany(CartLine::class);
    }
}
