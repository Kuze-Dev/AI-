<?php

declare(strict_types=1);

namespace Domain\Review\Models;

use Domain\Customer\Models\Customer;
use Illuminate\Database\Eloquent\Model;

/**
 * Domain\Review\Models\ReviewLike
 *
 * @property-read Customer|null $customer
 * @property-read \Domain\Review\Models\Review|null $review
 * @method static \Illuminate\Database\Eloquent\Builder|ReviewLike newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ReviewLike newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ReviewLike query()
 * @mixin \Eloquent
 */
class ReviewLike extends Model
{
    protected $fillable = [
        'customer_id',
    ];

    public function review()
    {
        return $this->belongsTo(Review::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
