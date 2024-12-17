<?php

declare(strict_types=1);

namespace Domain\Cart\Models;

use Domain\Customer\Models\Customer;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Support\ConstraintsRelationships\Attributes\OnDeleteCascade;
use Support\ConstraintsRelationships\ConstraintsRelationships;

/**
 * Domain\Cart\Models\Cart
 *
 * @property int $id
 * @property int $customer_id
 * @property string $uuid
 * @property string|null $coupon_code
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Domain\Cart\Models\CartLine> $cartLines
 * @property-read int|null $cart_lines_count
 * @property-read Customer|null $customer
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Cart newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Cart newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Cart query()
 * @method static \Illuminate\Database\Eloquent\Builder|Cart whereCouponCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cart whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cart whereCustomerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cart whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cart whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cart whereUuid($value)
 *
 * @mixin \Eloquent
 */
#[OnDeleteCascade(['cartLines'])]
class Cart extends Model
{
    use ConstraintsRelationships;
    use HasFactory;
    use LogsActivity;

    protected $fillable = [
        'uuid',
        'customer_id',
        'session_id',
        'coupon_code',
    ];

    #[\Override]
    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    /** @return \Illuminate\Database\Eloquent\Relations\HasMany<\Domain\Cart\Models\CartLine> */
    public function cartLines(): HasMany
    {
        return $this->hasMany(CartLine::class)->whereNull('checked_out_at')->latest();
    }

    /** @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\Domain\Customer\Models\Customer, $this> */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
