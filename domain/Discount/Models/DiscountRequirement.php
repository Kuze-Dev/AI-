<?php

declare(strict_types=1);

namespace Domain\Discount\Models;

use Domain\Discount\Enums\DiscountRequirementType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Domain\Discount\Models\DiscountRequirement
 *
 * @property int $id
 * @property int $discount_id
 * @property DiscountRequirementType|null $requirement_type
 * @property int|null $minimum_amount
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Domain\Discount\Models\Discount|null $discount
 *
 * @method static \Illuminate\Database\Eloquent\Builder|DiscountRequirement newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DiscountRequirement newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DiscountRequirement onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|DiscountRequirement query()
 * @method static \Illuminate\Database\Eloquent\Builder|DiscountRequirement whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DiscountRequirement whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DiscountRequirement whereDiscountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DiscountRequirement whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DiscountRequirement whereMinimumAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DiscountRequirement whereRequirementType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DiscountRequirement whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DiscountRequirement withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|DiscountRequirement withoutTrashed()
 *
 * @mixin \Eloquent
 */
class DiscountRequirement extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'discount_id',
        'requirement_type',
        'minimum_amount',
    ];

    protected function casts(): array
    {
        return [
            'requirement_type' => DiscountRequirementType::class,
            'minimum_amount' => 'float',
        ];
    }

    /** @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\Domain\Discount\Models\Discount, $this> */
    public function discount(): BelongsTo
    {
        return $this->belongsTo(Discount::class);
    }
}
