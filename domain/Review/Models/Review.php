<?php

declare(strict_types=1);

namespace Domain\Review\Models;

use Domain\Customer\Models\Customer;
use Domain\Order\Models\Order;
use Domain\Order\Models\OrderLine;
use Domain\Product\Models\Product;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * Domain\Review\Models\Review
 *
 * @property int $id
 * @property int $product_id
 * @property int $order_id
 * @property int $order_line_id
 * @property int|null $customer_id
 * @property int $rating
 * @property string|null $customer_name
 * @property string|null $customer_email
 * @property string|null $comment
 * @property array|null $data
 * @property bool $is_anonymous
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Customer|null $customer
 * @property-read \Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection<int, Media> $media
 * @property-read int|null $media_count
 * @property-read Order|null $order
 * @property-read OrderLine|null $order_line
 * @property-read Product|null $product
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Domain\Review\Models\ReviewLike> $review_likes
 * @property-read int|null $review_likes_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Review newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Review newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Review query()
 * @method static \Illuminate\Database\Eloquent\Builder|Review whereComment($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Review whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Review whereCustomerEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Review whereCustomerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Review whereCustomerName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Review whereData($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Review whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Review whereIsAnonymous($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Review whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Review whereOrderLineId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Review whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Review whereRating($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Review whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class Review extends Model implements HasMedia
{
    /** @use InteractsWithMedia<\Spatie\MediaLibrary\MediaCollections\Models\Media> */
    use InteractsWithMedia;

    protected $fillable = [
        'comment',
        'data',
        'customer_name',
        'customer_email',
        'customer_id',
        'rating',
        'order_line_id',
        'is_anonymous',
        'order_id',
        'product_id',
    ];

    protected function casts(): array
    {
        return [
            'data' => 'array',
            'is_anonymous' => 'bool',
        ];
    }

    #[\Override]
    public function registerMediaCollections(): void
    {
        $registerMediaConversions = function (Media $media) {
            $this->addMediaConversion('preview');
        };

        $this->addMediaCollection('review_product_media')
            ->registerMediaConversions($registerMediaConversions);
    }

    /** @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\Domain\Product\Models\Product, \Domain\Review\Models\Review> */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /** @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\Domain\Customer\Models\Customer, \Domain\Review\Models\Review>*/
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    /** @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\Domain\Order\Models\Order, \Domain\Review\Models\Review> */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /** @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\Domain\Order\Models\OrderLine, \Domain\Review\Models\Review> */
    public function order_line()
    {
        return $this->belongsTo(OrderLine::class);
    }

    /** @return \Illuminate\Database\Eloquent\Relations\HasMany<\Domain\Review\Models\ReviewLike> */
    public function review_likes()
    {
        return $this->hasMany(ReviewLike::class);
    }
}
