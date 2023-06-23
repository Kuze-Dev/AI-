<?php

declare(strict_types=1);

namespace Domain\Discount\Models;

use Domain\Discount\Enums\DiscountType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Domain\Discount\Models\Discount
 *
 * @property int $id
 * @property string $slug
 * @property string $name
 * @property string|null $description
 * @property DiscountType $type
 * @property mixed $status
 * @property int $max_uses
 * @property int $max_uses_per_user
 * @property \Illuminate\Support\Carbon $valid_start_at
 * @property \Illuminate\Support\Carbon|null $valid_end_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Domain\Discount\Models\DiscountCondition> $DiscountConditions
 * @property-read int|null $discount_conditions_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Domain\Discount\Models\DiscountCode> $discountCodes
 * @property-read int|null $discount_codes_count
 * @method static \Illuminate\Database\Eloquent\Builder|Discount newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Discount newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Discount query()
 * @method static \Illuminate\Database\Eloquent\Builder|Discount whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Discount whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Discount whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Discount whereMaxUses($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Discount whereMaxUsesPerUser($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Discount whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Discount whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Discount whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Discount whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Discount whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Discount whereValidEndAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Discount whereValidStartAt($value)
 * @mixin \Eloquent
 */
class Discount extends Model
{
    protected $fillable = [
        'slug',
        'name',
        'description',
        'type',
        'status',
        'max_uses',
        'max_uses_per_user',
        'valid_start_at',
        'valid_end_at',
    ];

    protected $casts = [
        'type' => DiscountType::class,
        'status' => DiscountStatus::class,
        'max_uses' => 'int',
        'max_uses_per_user' => 'int',
        'valid_start_at' => 'datetime',
        'valid_end_at' => 'datetime',
    ];

    public function discountCodes(): HasMany
    {
        return $this->hasMany(DiscountCode::class);
    }

    public function DiscountConditions(): HasMany
    {
        return $this->hasMany(DiscountCondition::class);
    }
}
