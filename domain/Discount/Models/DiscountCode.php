<?php

declare(strict_types=1);

namespace Domain\Discount\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Domain\Discount\Models\DiscountCode
 *
 * @property int $id
 * @property int $discount_id
 * @property string $code
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Domain\Discount\Models\Discount|null $discount
 * @method static \Illuminate\Database\Eloquent\Builder|DiscountCode newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DiscountCode newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DiscountCode query()
 * @method static \Illuminate\Database\Eloquent\Builder|DiscountCode whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DiscountCode whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DiscountCode whereDiscountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DiscountCode whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DiscountCode whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class DiscountCode extends Model
{
    protected $fillable = [
        'discount_id',
        'code',
    ];

    public function discount(): BelongsTo
    {
        return $this->belongsTo(Discount::class);
    }
}
