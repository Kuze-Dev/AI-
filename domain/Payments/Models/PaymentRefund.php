<?php

declare(strict_types=1);

namespace Domain\Payments\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/**
 * Domain\Payments\Models\Payment
 *
 * @property int $id
 * @property int $payment_id
 * @property string $refund_id
 * @property string $amount
 * @property string $status
 * @property string|null $transaction_id
 * @property string|null $remarks
 * @property string|null $message
 * @property array|null $refund_details
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection<int, \Spatie\MediaLibrary\MediaCollections\Models\Media> $media
 * @property-read int|null $media_count
 * @property-read \Domain\Payments\Models\Payment|null $payment
 *
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentRefund newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentRefund newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentRefund query()
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentRefund whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentRefund whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentRefund whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentRefund whereMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentRefund wherePaymentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentRefund whereRefundDetails($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentRefund whereRefundId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentRefund whereRemarks($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentRefund whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentRefund whereTransactionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentRefund whereUpdatedAt($value)
 *
 * @mixin Eloquent
 */
class PaymentRefund extends Model implements HasMedia
{
    use InteractsWithMedia;

    /**
     * Declare columns
     * that are mass assignable.
     */
    protected $fillable = [
        'id',
        'payment_id',
        'refund_id',
        'amount',
        'transaction_id',
        'status',
        'remarks',
        'message',
        'refund_details',
    ];

    protected $with = [
        'media',
    ];

    protected $casts = [
        'refund_details' => 'array',
    ];

    /** @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\Domain\Payments\Models\Payment, self> */
    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    #[\Override]
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('image')
            ->singleFile();
    }
}
