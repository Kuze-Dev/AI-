<?php

declare(strict_types=1);

namespace Domain\Cart\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * Domain\Cart\Models\CartLine
 *
 * @property int $id
 * @property int $cart_id
 * @property string $purchasable_type
 * @property int $purchasable_id
 * @property string $uuid
 * @property int $quantity
 * @property array|null $remarks
 * @property string|null $checkout_reference
 * @property \Illuminate\Support\Carbon|null $checkout_expiration
 * @property \Illuminate\Support\Carbon|null $checked_out_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Domain\Cart\Models\Cart|null $cart
 * @property-read \Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection<int, Media> $media
 * @property-read int|null $media_count
 * @property-read Model|Eloquent $purchasable
 *
 * @method static \Illuminate\Database\Eloquent\Builder|CartLine newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CartLine newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CartLine query()
 * @method static \Illuminate\Database\Eloquent\Builder|CartLine whereCartId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CartLine whereCheckedOutAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CartLine whereCheckoutExpiration($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CartLine whereCheckoutReference($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CartLine whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CartLine whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CartLine wherePurchasableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CartLine wherePurchasableType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CartLine whereQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CartLine whereRemarks($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CartLine whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CartLine whereUuid($value)
 *
 * @mixin Eloquent
 */
class CartLine extends Model implements HasMedia
{
    use HasFactory;

    /** @use InteractsWithMedia<\Spatie\MediaLibrary\MediaCollections\Models\Media> */
    use InteractsWithMedia;

    use LogsActivity;

    protected $fillable = [
        'uuid',
        'cart_id',
        'purchasable_id',
        'purchasable_type',
        'quantity',
        'remarks',
        'checkout_reference',
        'checked_out_at',
    ];

    protected function casts(): array
    {
        return [
            'remarks' => 'array',
            'checkout_expiration' => 'datetime',
            'checked_out_at' => 'datetime',
        ];
    }

    #[\Override]
    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    /** @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\Domain\Cart\Models\Cart, $this> */
    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class);
    }

    /** @return MorphTo<Model, $this> */
    public function purchasable(): MorphTo
    {
        return $this->morphTo();
    }

    #[\Override]
    public function registerMediaCollections(): void
    {
        $registerMediaConversions = function (Media $media) {
            $this->addMediaConversion('preview');
        };

        $this->addMediaCollection('cart_line_notes')
            ->onlyKeepLatest(5)
            ->registerMediaConversions($registerMediaConversions);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
