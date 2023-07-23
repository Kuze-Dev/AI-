<?php

declare(strict_types=1);

namespace Domain\Discount\Models;

use Domain\Discount\Enums\DiscountStatus;
use Domain\Discount\Enums\DiscountConditionType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Domain\Discount\Models\Discount
 *
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string|null $description
 * @property string $code
 * @property DiscountStatus $status
 * @property int|null $max_uses
 * @property \Illuminate\Support\Carbon $valid_start_at
 * @property \Illuminate\Support\Carbon|null $valid_end_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property DiscountConditionType $type
 * @property-read \Domain\Discount\Models\DiscountCondition|null $discountCondition
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Domain\Discount\Models\DiscountLimit> $discountLimits
 * @property-read int|null $discount_limits_count
 * @property-read \Domain\Discount\Models\DiscountRequirement|null $discountRequirement
 * @method static \Illuminate\Database\Eloquent\Builder|Discount newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Discount newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Discount onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Discount query()
 * @method static \Illuminate\Database\Eloquent\Builder|Discount whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Discount whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Discount whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Discount whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Discount whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Discount whereMaxUses($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Discount whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Discount whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Discount whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Discount whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Discount whereValidEndAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Discount whereValidStartAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Discount withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Discount withoutTrashed()
 * @mixin \Eloquent
 */
class Discount extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'code',
        'status',
        'max_uses',
        // 'max_uses_per_user',
        'valid_start_at',
        'valid_end_at',
    ];

    protected $casts = [
        'type' => DiscountConditionType::class,
        'status' => DiscountStatus::class,
        'max_uses' => 'int',
        // 'max_uses_per_user' => 'int',
        'valid_start_at' => 'datetime',
        'valid_end_at' => 'datetime',
    ];

    public function getRouteKeyName(): string
    {
        return 'code';
    }

    // public function discountCodes(): HasMany
    // {
    //     return $this->hasMany(DiscountCode::class);
    // }

    /** @return \Illuminate\Database\Eloquent\Relations\HasOne<\Domain\Discount\Models\DiscountCondition> */
    public function discountCondition(): HasOne
    {
        return $this->hasOne(DiscountCondition::class);
    }

    /** @return \Illuminate\Database\Eloquent\Relations\HasOne<\Domain\Discount\Models\DiscountRequirement> */
    public function discountRequirement(): HasOne
    {
        return $this->hasOne(DiscountRequirement::class);
    }

    public function discountLimits(): HasMany
    {
        return $this->hasMany(DiscountLimit::class, 'discountable');
    }
}
