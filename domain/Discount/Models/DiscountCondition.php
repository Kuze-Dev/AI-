<?php

declare(strict_types=1);

namespace Domain\Discount\Models;

use Domain\Discount\Enums\DiscountAmountType;
use Domain\Discount\Enums\DiscountConditionType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Domain\Discount\Models\DiscountCondition
 *
 * @property DiscountConditionType $discount_type
 * @property DiscountAmountType $damount_type
 * @property-read \Domain\Discount\Models\Discount|null $discount
 * @method static \Illuminate\Database\Eloquent\Builder|DiscountCondition newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DiscountCondition newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DiscountCondition query()
 * @mixin \Eloquent
 */
class DiscountCondition extends Model
{
    protected $fillable = [
        'discount_id',
        'discount_type',
        'amount_type',
        'amount',
    ];

    protected $casts = [
        'discount_type' => DiscountConditionType::class,
        'amount_type' => DiscountAmountType::class,
        'amount' => 'int',
    ];

    public function discount(): BelongsTo
    {
        return $this->belongsTo(Discount::class);
    }
}
