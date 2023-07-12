<?php

declare(strict_types=1);

namespace Domain\Order\Models;

use Domain\Customer\Models\Customer;
use Domain\Order\Enums\OrderAddressTypes;
use Domain\Order\Enums\OrderStatuses;
use Illuminate\Database\Eloquent\Model;
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
 * @property float $currency_exchange_rate
 * @property string $reference
 * @property float $tax_total
 * @property float $sub_total
 * @property float $discount_total
 * @property float $shipping_total
 * @property float $total
 * @property string|null $notes
 * @property string $shipping_method
 * @property string $shipping_details
 * @property string $payment_method
 * @property string $payment_details
 * @property string|null $payment_status
 * @property string|null $payment_message
 * @property bool $is_paid
 * @property OrderStatuses $status
 * @property string|null $cancelled_reason
 * @property \Illuminate\Support\Carbon|null $cancelled_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Activitylog\Models\Activity> $activities
 * @property-read int|null $activities_count
 * @property-read \Domain\Order\Models\OrderAddress|null $billing_address
 * @property-read Customer|null $customer
 * @property-read \Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection<int, Media> $media
 * @property-read int|null $media_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Domain\Order\Models\OrderLine> $order_lines
 * @property-read int|null $order_lines_count
 * @property-read \Domain\Order\Models\OrderAddress|null $shipping_address
 * @method static \Illuminate\Database\Eloquent\Builder|Order newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Order newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Order query()
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereCancelledAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereCancelledReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereCurrencyCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereCurrencyExchangeRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereCurrencyName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereCustomerEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereCustomerFirstName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereCustomerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereCustomerLastName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereCustomerMobile($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereDiscountTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereIsPaid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order wherePaymentDetails($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order wherePaymentMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order wherePaymentMethod($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order wherePaymentStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereReference($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereShippingDetails($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereShippingMethod($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereShippingTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereSubTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereTaxTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Order extends Model implements HasMedia
{
    use LogsActivity;
    use InteractsWithMedia;

    protected $fillable = [
        'customer_id',
        'customer_first_name',
        'customer_last_name',
        'customer_mobile',
        'customer_email',
        'currency_code',
        'currency_name',
        'currency_exchange_rate',
        'reference',
        'tax_total',
        'sub_total',
        'discount_total',
        'shipping_total',
        'total',
        'notes',
        'shipping_method',
        'shipping_details',
        'payment_method',
        'payment_details',
        'payment_status',
        'payment_message',
        'is_paid',
        'status',
        'cancelled_reason',
        'cancelled_at',
    ];

    protected $casts = [
        'currency_exchange_rate' => 'float',
        'tax_total' => 'float',
        'sub_total' => 'float',
        'discount_total' => 'float',
        'shipping_total' => 'float',
        'total' => 'float',
        'is_paid' => 'boolean',
        'status' => OrderStatuses::class,
        'cancelled_at' => 'datetime',
    ];

    public function getRouteKeyName(): string
    {
        return 'reference';
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function order_lines()
    {
        return $this->hasMany(OrderLine::class);
    }

    public function shipping_address()
    {
        return $this->hasOne(OrderAddress::class)->where('type', OrderAddressTypes::SHIPPING);
    }

    public function billing_address()
    {
        return $this->hasOne(OrderAddress::class)->where('type', OrderAddressTypes::BILLING);
    }

    public function registerMediaCollections(): void
    {
        $registerMediaConversions = function (Media $media) {
            $this->addMediaConversion('preview');
        };

        $this->addMediaCollection('bank_proof_images')
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
