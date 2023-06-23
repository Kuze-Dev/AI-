<?php

declare(strict_types=1);

namespace Domain\Discount\Models;

use Domain\Discount\Enums\DiscountConditionType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Domain\Discount\Models\DiscountCondition
 *
 * @property int $id
 * @property int $discount_id
 * @property DiscountConditionType $type
 * @property array|null $data
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Domain\Discount\Models\Discount|null $discount
 * @method static \Illuminate\Database\Eloquent\Builder|DiscountCondition newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DiscountCondition newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DiscountCondition query()
 * @method static \Illuminate\Database\Eloquent\Builder|DiscountCondition whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DiscountCondition whereData($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DiscountCondition whereDiscountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DiscountCondition whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DiscountCondition whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DiscountCondition whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class DiscountCondition extends Model
{
    protected $fillable = [
        'discount_id',
        'type',
        'data',
    ];

    protected $casts = [
        'type' => DiscountConditionType::class,
        'data' => 'array',
    ];

    public function discount(): BelongsTo
    {
        return $this->belongsTo(Discount::class);
    }
}
