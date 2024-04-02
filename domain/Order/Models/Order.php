<?php

declare(strict_types=1);

namespace Domain\Order\Models;

use Domain\Customer\Models\Customer;
use Domain\Order\Enums\OrderAddressTypes;
use Domain\Order\Enums\OrderStatuses;
use Domain\Payments\Interfaces\PayableInterface;
use Domain\Payments\Models\Traits\HasPayments;
use Domain\ShippingMethod\Models\ShippingMethod;
use Domain\Taxation\Enums\PriceDisplay;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Notifications\Notifiable;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * Domain\Order\Models\Order
 *
 * @property int $id
 * @property int $customer_id
 * @property string $customer_first_name
 * @property string $customer_last_name
 * @property string $customer_mobile
 * @property string $customer_email
 * @property string $currency_code
 * @property string $currency_name
 * @property string $currency_symbol
 * @property string $reference
 * @property float|null $tax_total
 * @property float|null $tax_percentage
 * @property PriceDisplay|null $tax_display
 * @property float $sub_total
 * @property float $discount_total
 * @property int|null $discount_id
 * @property string|null $discount_code
 * @property float $shipping_total
 * @property int $shipping_method_id
 * @property float $total
 * @property string|null $notes
 * @property bool $is_paid
 * @property OrderStatuses $status
 * @property string|null $cancelled_reason
 * @property \Illuminate\Support\Carbon|null $cancelled_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Activitylog\Models\Activity> $activities
 * @property-read int|null $activities_count
 * @property-read \Domain\Order\Models\OrderAddress $billingAddress
 * @property-read Customer|null $customer
 * @property-read \Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection<int, Media> $media
 * @property-read int|null $media_count
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Domain\Order\Models\OrderLine> $orderLines
 * @property-read int|null $order_lines_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Domain\Payments\Models\Payment> $payments
 * @property-read int|null $payments_count
 * @property-read \Domain\Order\Models\OrderAddress $shippingAddress
 * @property-read ShippingMethod|null $shippingMethod
 *
 * @method static Builder|Order newModelQuery()
 * @method static Builder|Order newQuery()
 * @method static Builder|Order query()
 * @method static Builder|Order whereCancelledAt($value)
 * @method static Builder|Order whereCancelledReason($value)
 * @method static Builder|Order whereCreatedAt($value)
 * @method static Builder|Order whereCurrencyCode($value)
 * @method static Builder|Order whereCurrencyName($value)
 * @method static Builder|Order whereCurrencySymbol($value)
 * @method static Builder|Order whereCustomerEmail($value)
 * @method static Builder|Order whereCustomerFirstName($value)
 * @method static Builder|Order whereCustomerId($value)
 * @method static Builder|Order whereCustomerLastName($value)
 * @method static Builder|Order whereCustomerMobile($value)
 * @method static Builder|Order whereDiscountCode($value)
 * @method static Builder|Order whereDiscountId($value)
 * @method static Builder|Order whereDiscountTotal($value)
 * @method static Builder|Order whereId($value)
 * @method static Builder|Order whereIsPaid($value)
 * @method static Builder|Order whereNotes($value)
 * @method static Builder|Order whereReference($value)
 * @method static Builder|Order whereShippingMethodId($value)
 * @method static Builder|Order whereShippingTotal($value)
 * @method static Builder|Order whereStatus($value)
 * @method static Builder|Order whereSubTotal($value)
 * @method static Builder|Order whereTaxDisplay($value)
 * @method static Builder|Order whereTaxPercentage($value)
 * @method static Builder|Order whereTaxTotal($value)
 * @method static Builder|Order whereTotal($value)
 * @method static Builder|Order whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class Order extends Model implements HasMedia, PayableInterface
{
    use HasPayments;
    use InteractsWithMedia;
    use LogsActivity;
    use Notifiable;

    protected $fillable = [
        'customer_id',
        'customer_first_name',
        'customer_last_name',
        'customer_mobile',
        'customer_email',
        'currency_code',
        'currency_name',
        'currency_symbol',
        'reference',
        'tax_total',
        'tax_display',
        'tax_percentage',
        'sub_total',
        'discount_total',
        'discount_id',
        'discount_code',
        'shipping_total',
        'shipping_method_id',
        'total',
        'notes',
        'is_paid',
        'status',
        'cancelled_reason',
        'cancelled_at',
    ];

    protected $casts = [
        'tax_total' => 'float',
        'tax_display' => PriceDisplay::class,
        'tax_percentage' => 'float',
        'sub_total' => 'float',
        'discount_total' => 'float',
        'shipping_total' => 'float',
        'total' => 'float',
        'is_paid' => 'boolean',
        'status' => OrderStatuses::class,
        'cancelled_at' => 'datetime',
    ];

    #[\Override]
    public function getRouteKeyName(): string
    {
        return 'reference';
    }

    /** @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\Domain\Customer\Models\Customer, \Domain\Order\Models\Order> */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /** @return \Illuminate\Database\Eloquent\Relations\HasMany<\Domain\Order\Models\OrderLine>*/
    public function orderLines(): HasMany
    {
        return $this->hasMany(OrderLine::class);
    }

    /** @return \Illuminate\Database\Eloquent\Relations\HasOne<\Domain\Order\Models\OrderAddress> */
    public function shippingAddress(): HasOne
    {
        return $this->hasOne(OrderAddress::class)->where('type', OrderAddressTypes::SHIPPING);
    }

    /** @return \Illuminate\Database\Eloquent\Relations\HasOne<\Domain\Order\Models\OrderAddress> */
    public function billingAddress(): HasOne
    {
        return $this->hasOne(OrderAddress::class)->where('type', OrderAddressTypes::BILLING);
    }

    /** @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\Domain\ShippingMethod\Models\ShippingMethod, \Domain\Order\Models\Order> */
    public function shippingMethod(): BelongsTo
    {
        return $this->belongsTo(ShippingMethod::class, 'shipping_method_id');
    }

    #[\Override]
    public function registerMediaCollections(): void
    {
        $registerMediaConversions = function () {
            $this->addMediaConversion('preview');
        };

        $this->addMediaCollection('bank_proof_images')
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

    #[\Override]
    public function getReferenceNumber(): string
    {
        return $this->reference;
    }

    /** @return Attribute<string, never> */
    protected function customerFullName(): Attribute
    {
        return Attribute::get(
            fn (): string => "{$this->customer_first_name} {$this->customer_last_name}"
        );
    }
}
