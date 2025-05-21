<?php

declare(strict_types=1);

namespace Domain\Discount\Models;

use Domain\Discount\Enums\DiscountAmountType;
use Domain\Discount\Enums\DiscountConditionType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Domain\Discount\Models\DiscountCondition
 *
 * @property int $id
 * @property int $discount_id
 * @property DiscountConditionType $discount_type
 * @property DiscountAmountType $amount_type
 * @property int $amount
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Domain\Discount\Models\Discount|null $discount
 *
 * @method static \Illuminate\Database\Eloquent\Builder|DiscountCondition newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DiscountCondition newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DiscountCondition onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|DiscountCondition query()
 * @method static \Illuminate\Database\Eloquent\Builder|DiscountCondition whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DiscountCondition whereAmountType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DiscountCondition whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DiscountCondition whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DiscountCondition whereDiscountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DiscountCondition whereDiscountType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DiscountCondition whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DiscountCondition whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DiscountCondition withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|DiscountCondition withoutTrashed()
 *
 * @mixin \Eloquent
 */
class DiscountCondition extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'discount_id',
        'discount_type',
        'amount_type',
        'amount',
    ];

    protected function casts(): array
    {
        return [
            'discount_type' => DiscountConditionType::class,
            'amount_type' => DiscountAmountType::class,
            'amount' => 'float',
        ];
    }

    /** @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\Domain\Discount\Models\Discount, $this> */
    public function discount(): BelongsTo
    {
        return $this->belongsTo(Discount::class);
    }
}
