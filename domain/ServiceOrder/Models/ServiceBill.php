<?php

declare(strict_types=1);

namespace Domain\ServiceOrder\Models;

use Akaunting\Money\Money;
use App\Casts\MoneyCast;
use Domain\PaymentMethod\Models\PaymentMethod;
use Domain\Payments\Interfaces\PayableInterface;
use Domain\Payments\Models\Payment;
use Domain\Payments\Models\Traits\HasPayments;
use Domain\ServiceOrder\Enums\ServiceBillStatus;
use Domain\ServiceOrder\Enums\ServiceTransactionStatus;
use Domain\ServiceOrder\Queries\ServiceBillQueryBuilder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/**
 * Domain\ServiceOrder\Models\ServiceBill
 *
 * @property int $id
 * @property int $service_order_id
 * @property string $reference
 * @property \Illuminate\Support\Carbon|null $bill_date
 * @property \Illuminate\Support\Carbon|null $due_date
 * @property string $currency
 * @property float $service_price
 * @property array $additional_charges
 * @property float $sub_total
 * @property string|null $tax_display
 * @property float $tax_percentage
 * @property float $tax_total
 * @property float $total_amount
 * @property float $total_balance
 * @property ServiceBillStatus $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Domain\Payments\Models\Payment> $payments
 * @property-read int|null $payments_count
 * @property-read \Domain\ServiceOrder\Models\ServiceOrder|null $serviceOrder
 * @property-read \Illuminate\Database\Eloquent\Collection<int, ServiceTransaction>|null $serviceTransactions
 * @property-read bool $is_paid
 * @property-read bool $is_initial
 *
 * @method static ServiceBillQueryBuilder|ServiceBill newModelQuery()
 * @method static ServiceBillQueryBuilder|ServiceBill newQuery()
 * @method static ServiceBillQueryBuilder|ServiceBill query()
 * @method static ServiceBillQueryBuilder|ServiceBill whereAdditionalCharges($value)
 * @method static ServiceBillQueryBuilder|ServiceBill whereBillDate($value)
 * @method static ServiceBillQueryBuilder|ServiceBill whereCreatedAt($value)
 * @method static ServiceBillQueryBuilder|ServiceBill whereDueDate($value)
 * @method static ServiceBillQueryBuilder|ServiceBill whereHasBillingDate()
 * @method static ServiceBillQueryBuilder|ServiceBill whereHasDueDate()
 * @method static ServiceBillQueryBuilder|ServiceBill whereId($value)
 * @method static ServiceBillQueryBuilder|ServiceBill whereNotifiable()
 * @method static ServiceBillQueryBuilder|ServiceBill wherePendingStatus()
 * @method static ServiceBillQueryBuilder|ServiceBill whereReference($value)
 * @method static ServiceBillQueryBuilder|ServiceBill whereServiceOrderId($value)
 * @method static ServiceBillQueryBuilder|ServiceBill whereServicePrice($value)
 * @method static ServiceBillQueryBuilder|ServiceBill whereStatus($value)
 * @method static ServiceBillQueryBuilder|ServiceBill whereSubTotal($value)
 * @method static ServiceBillQueryBuilder|ServiceBill whereTaxDisplay($value)
 * @method static ServiceBillQueryBuilder|ServiceBill whereTaxPercentage($value)
 * @method static ServiceBillQueryBuilder|ServiceBill whereTaxTotal($value)
 * @method static ServiceBillQueryBuilder|ServiceBill whereTotalAmount($value)
 * @method static ServiceBillQueryBuilder|ServiceBill whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class ServiceBill extends Model implements HasMedia, PayableInterface
{
    use HasPayments;

    /** @use InteractsWithMedia<\Spatie\MediaLibrary\MediaCollections\Models\Media> */
    use InteractsWithMedia;

    use SoftDeletes;

    protected $fillable = [
        'service_order_id',
        'reference',
        'bill_date',
        'due_date',
        'currency',
        'service_price',
        'additional_charges',
        'sub_total',
        'tax_display',
        'tax_percentage',
        'tax_total',
        'total_amount',
        'total_balance',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'bill_date' => 'datetime',
            'due_date' => 'datetime',
            'additional_charges' => 'array',
            'service_price' => MoneyCast::class,
            'sub_total' => MoneyCast::class,
            'tax_percentage' => 'float',
            'tax_total' => MoneyCast::class,
            'total_amount' => MoneyCast::class,
            'total_balance' => MoneyCast::class,
            'status' => ServiceBillStatus::class,
        ];
    }

    #[\Override]
    public function getRouteKeyName(): string
    {
        return 'reference';
    }

    #[\Override]
    public function getReferenceNumber(): string
    {
        return $this->reference;
    }

    #[\Override]
    public function newEloquentBuilder($query): ServiceBillQueryBuilder
    {
        return new ServiceBillQueryBuilder($query);
    }

    #[\Override]
    public function registerMediaCollections(): void
    {
        $registerMediaConversions = function () {
            $this->addMediaConversion('preview');
        };

        $this->addMediaCollection('service_bill_bank_proof_image')
            ->onlyKeepLatest(5)
            ->registerMediaConversions($registerMediaConversions);
    }

    /** @return Attribute<Money, never> */
    protected function getTotalAdditionalCharges(): Attribute
    {
        $total = money(0);

        return Attribute::get(
            /** @return Money */
            function ($value) use ($total) {
                foreach ($this->additional_charges as $additional_charge) {
                    $total = $total->add(
                        money((int) $additional_charge['price'])
                            ->multiply((int) $additional_charge['quantity'])
                    );
                }

                return $total;
            }
        );
    }

    /** @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\Domain\ServiceOrder\Models\ServiceOrder, $this> */
    public function serviceOrder(): BelongsTo
    {
        return $this->belongsTo(ServiceOrder::class);
    }

    /** @return \Illuminate\Database\Eloquent\Relations\HasMany<\Domain\ServiceOrder\Models\ServiceTransaction, $this> */
    public function serviceTransactions(): HasMany
    {
        return $this->hasMany(ServiceTransaction::class);
    }

    public function latestTransaction(): ?ServiceTransaction
    {
        return $this->serviceTransactions()->latest()->first();
    }

    public function latestTransactionPaid(): ?ServiceTransaction
    {
        /** @var \Domain\ServiceOrder\Models\ServiceTransaction $serviceTransaction */
        $serviceTransaction = $this->latestTransaction();

        return filled($serviceTransaction) && $serviceTransaction->status === ServiceTransactionStatus::PAID
            ? $serviceTransaction
            : null;
    }

    public function latestPayment(): ?Payment
    {
        return $this->payments()->latest()->first();
    }

    public function paymentMethod(): ?PaymentMethod
    {
        /** @var \Domain\ServiceOrder\Models\ServiceTransaction $serviceTransaction */
        $serviceTransaction = $this->latestTransaction();

        return filled($serviceTransaction) ? $serviceTransaction->payment_method : null;
    }

    /** @return Attribute<bool, never> */
    protected function isPaid(): Attribute
    {
        return Attribute::get(
            fn (mixed $value) => $this->status === ServiceBillStatus::PAID
        );
    }

    /** @return Attribute<bool, never> */
    protected function isInitial(): Attribute
    {
        return Attribute::get(
            fn (mixed $value) => is_null($this->bill_date) && is_null($this->due_date)
        );
    }
}
