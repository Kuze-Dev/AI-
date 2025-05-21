<?php

declare(strict_types=1);

namespace Domain\Review\Models;

use Domain\Customer\Models\Customer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Domain\Review\Models\ReviewLike
 *
 * @property int $id
 * @property int $review_id
 * @property int $customer_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Customer|null $customer
 * @property-read \Domain\Review\Models\Review|null $review
 *
 * @method static \Illuminate\Database\Eloquent\Builder|ReviewLike newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ReviewLike newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ReviewLike query()
 * @method static \Illuminate\Database\Eloquent\Builder|ReviewLike whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ReviewLike whereCustomerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ReviewLike whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ReviewLike whereReviewId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ReviewLike whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class ReviewLike extends Model
{
    protected $fillable = [
        'customer_id',
    ];

    /** @return BelongsTo<\Domain\Review\Models\Review, $this> */
    public function review(): BelongsTo
    {
        return $this->belongsTo(Review::class);
    }

    /** @return BelongsTo<\Domain\Customer\Models\Customer, $this> */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}
